<?php

namespace App\Component\Collect;

use App\Component\Compare;
use App\Model\Novelsearch\Chapter;
use App\Model\Novelsearch\Log;
use App\Model\Novelsearch\Novel;
use App\Model\Novelsearch\Site;
use App\Model\User\Mark;
use Kuxin\Config;
use Kuxin\DI;

class Collect extends Base
{
    
    /**
     * @var Novel
     */
    protected $novelModel;
    
    /**
     * @var Chapter
     */
    protected $chapterModel;
    
    /**
     * @var Log
     */
    protected $logModel;
    
    /**
     * @var Site
     */
    protected $siteModel;
    
    /**
     * 日志信息
     *
     * @var
     */
    protected $log;
    
    /**
     * 小说信息
     *
     * @var
     */
    protected $novel;
    
    /**
     * 要更新的小说信息
     *
     * @var
     */
    protected $novelData = [];
    
    /**
     * 站点信息
     *
     * @var
     */
    protected $site;
    
    /**
     * @var \App\Component\Compare
     */
    protected $compare;
    
    /**
     * 锁定时间
     *
     * @var
     */
    protected $lockTime = 600;
    
    /**
     * @var bool
     */
    protected $isCron = false;
    
    
    /**
     * Collect constructor.
     *
     * @param        $id
     * @param string $type
     */
    public function __construct($id = 0, $type = 'ruleid')
    {
        parent::__construct($id, $type);
        $this->chapterModel = Chapter::I();
        $this->novelModel   = Novel::I();
        $this->logModel     = Log::I();
        $this->siteModel    = Site::I();
        $this->site         = $this->siteModel->find($this->ruleInfo['siteid']);
        $this->compare      = new Compare();
    }
    
    
    public function cron($start, $end)
    {
        $this->isCron = true;
        $this->cache->set("collect_process_{$this->ruleId}.create", time());
        for ($page = $start; $page <= $end; $page++) {
            $this->cache->set("collect_process_{$this->ruleId}.page", "$page/[$start,$end]");
            $this->list($page);
        }
        $this->cache->remove("collect_process_{$this->ruleId}.create");
        $this->cache->remove("collect_process_{$this->ruleId}.page");
        $this->cache->remove("collect_process_{$this->ruleId}.list");
        $this->cache->remove("collect_process_{$this->ruleId}.novel");
        $this->cache->remove("collect_process_{$this->ruleId}.time");
        $this->cache->remove("collect_process_{$this->ruleId}.info");
    }
    
    /**
     * 采集列表
     *
     * @param $page
     * @return null|mixed
     */
    public function list($page = 1)
    {
        $list = $this->handler->getList($page);
        if ($list['status']) {
            $num = count($list['data']);
            $this->process('获取列表成功,获取到数目:' . $num, 'success');
            foreach ($list['data'] as $key => $item) {
                $this->novel = $this->novelData = [];
                if ($this->isCron) {
                    if (!$this->checkCronPid()) {
                        $this->process('规则有其他进程启动，终止本进程', 'error');
                        exit;
                    }
                    $this->cache->set("collect_process_{$this->ruleId}.novel", $item['novelname']);
                    $this->cache->set("collect_process_{$this->ruleId}.time", time());
                    $this->cache->set("collect_process_{$this->ruleId}.info", '开始处理');
                }
                $this->cache->set("collect_process_{$this->ruleId}.list", ($key + 1) . "/$num");
                if ($this->log = $this->logModel->getInfo($this->ruleInfo['siteid'], $item['novelid'])) {
                    if (!$this->log['novelid']) {
                        //新书
                        if (!$this->_addNewNovel($item['novelid'])) {
                            continue;
                        }
                    } elseif ($this->log['lastid'] == $item['chapterid']) {
                        //已存在
                        $this->process(sprintf("《%s》采集进度相同，跳过", $item['novelname']), 'warning');
                        continue;
                    } else {
                        $this->process(sprintf("《%s》获取采集进度成功", $item['novelname']), 'warning');
                        $this->novel = $this->novelModel->find($this->log['novelid']);
                    }
                    $this->log['lastsign'] = $item['chapterid'];
                    
                    if (!$this->lockNovel()) {
                        continue;
                    }
                    $this->_updateNovel();
                    $this->unLockNovel();
                } else {
                    $this->process(sprintf("获取《%s》[%s]采集进度失败", $item['novelname'], $item['novelid']), 'error');
                    return true;
                }
            }
        } else {
            $this->process($list['info'], 'error');
            return false;
        }
    }
    
    /**
     * 按照id采集
     *
     * @param mixed $fromid 目标站来源id
     */
    public function id($fromid)
    {
        if ($this->log = $this->logModel->getInfo($this->ruleInfo['siteid'], $fromid)) {
            if ($this->log['novelid']) {
                $this->novel = $this->novelModel->find($this->log['novelid']);
                $this->process(sprintf("《%s》获取采集进度成功", $this->novel['name']));
            } else {
                //新书
                $this->_addNewNovel($fromid);
            }
            $this->log['lastsign'] = $this->log['lastid'];
            $this->_updateNovel();
        } else {
            $this->process(sprintf("《%s》创建采集进度失败", $fromid), 'error');
        }
    }
    
    /**
     * 重拍章节
     */
    public function reOrderAll($novelInfo)
    {
        $logs = $this->logModel->where(['novelid' => $novelInfo['id']])->field('siteid,oid')->select();
        $logs = array_column($logs, 'oid', 'siteid');
        $this->process(sprintf("《%s》开始重拍,站点数目 %s", $novelInfo['name'], count($logs)));
        $sites = $this->siteModel->getNameOrder();
        $this->chapterModel->setTableId($novelInfo['id']);
        $matchlist = $this->chapterModel->field('id,oid,name')->where(['novelid' => $novelInfo['id'], 'siteid' => $novelInfo['siteid']])->order('id asc')->select();
        $oid       = 0;
        foreach ($matchlist as $k => $item) {
            $oid++;
            if ($item['oid'] != $oid) {
                $matchlist[$k]['oid'] = $oid;
                $this->chapterModel->where(['id' => $item['id']])->update(['oid' => $item['oid'], 'time' => $_SERVER['REQUEST_TIME']]);
            }
        }
        //按照权重重拍其他站点
        foreach ($sites as $siteid) {
            if ($siteid == $novelInfo['siteid'] || !isset($logs[$siteid]) || $siteid == $this->log['siteid']) {
                //跳过基准站 跳过未采集的 跳过当前站点
                continue;
            }
            $siteInfo = $this->siteModel->getCacheInfo($siteid);
            
            $data = $this->chapterModel->field('id,oid,name')->where(['novelid' => $novelInfo['id'], 'siteid' => $siteid])->order('id asc')->select();
            //还原
            foreach ($data as $k => $item) {
                $data[$k]['beforeoid'] = $data[$k]['oid'];
                $data[$k]['oid']       = 0;
            }
            //重排
            $newdata = $this->compare->order($data, $matchlist, ['name' => $novelInfo['name'], 'author' => $novelInfo['author']], $siteInfo['isoriginal'] > 0);
            //更新
            $max = 0;
            foreach ($newdata as $k => $item) {
                if ($max < $item['oid']) {
                    $max = $item['oid'];
                }
                if ($max > $oid) {
                    $matchlist[] = $item;
                    $oid         = $max;
                }
                if ($item['oid'] != $item['beforeoid']) {
                    $this->chapterModel->where(['id' => $item['id']])->update(['oid' => $item['oid'], 'time' => $_SERVER['REQUEST_TIME']]);
                }
            }
            $this->logModel->where(['novelid' => $novelInfo['id'], 'siteid' => $siteid])->update(['oid' => $max]);
            $this->process(sprintf("《%s》站点（ %s - %s ）重排完毕 章节数 %s", $novelInfo['name'], $siteInfo['id'], $siteInfo['name'], count($data)));
        }
        $this->process(sprintf("《%s》重拍结束", $novelInfo['name']));
        $maxchapter = $this->chapterModel->field('id,siteid,oid,name')->where(['novelid' => $novelInfo['id']])->order('oid desc,id desc')->find();
        $data       = [
            'lastsiteid'      => $maxchapter['siteid'],
            'lastchapterid'   => $maxchapter['oid'],
            'lastchaptername' => $maxchapter['name'],
            'lastupdate'      => time(),
        ];
        //更新书架
        Mark::I()->where(['novelid' => $this->novel['id'], 'chaptersign' => ['>', $maxchapter['oid']]])->update(['chaptersign' => $maxchapter['oid']]);
        $this->novelModel->where(['id' => $this->novel['id']])->update($data);
        //清楚缓存
        $this->novelModel->clearCache($this->novel['id']);
        $this->chapterModel->clearCache($this->novel['id'], $data['lastchapterid']);
        
    }
    
    /**
     * 重拍部分章节
     *
     * @param int $fromoid
     */
    protected function reOrderChapter($fromoid = 0)
    {
        $logs = $this->logModel->where(['novelid' => $this->novel['id']])->field('siteid,oid')->select();
        $logs = array_column($logs, 'oid', 'siteid');
        $this->process(sprintf("《%s》开始重拍,站点数目 %s", $this->novel['name'], count($logs)));
        $sites     = $this->siteModel->getNameOrder();
        $matchlist = $this->chapterModel->field('oid,name')->where(['novelid' => $this->novel['id'], 'siteid' => $this->novel['siteid'], 'oid' => ['>=', $fromoid]])->select();
        if ($this->novel['siteid'] == $this->log['siteid']) {
            $maxoid = $this->novel['orderid'];
        } else {
            //其他基准站
            $extlist   = $this->chapterModel->field('oid,name')->where(['novelid' => $this->novel['id'], 'siteid' => $this->log['siteid'], 'oid' => ['>', $this->novel['orderid']]])->select();
            $matchlist = array_merge($matchlist, $extlist);
            $maxoid    = $this->log['oid'];
        }
        //按照权重重拍其他站点
        foreach ($sites as $siteid) {
            if ($siteid == $this->novel['siteid'] || !isset($logs[$siteid]) || $siteid == $this->log['siteid']) {
                //跳过基准站 跳过未采集的 跳过当前站点
                continue;
            }
            $siteInfo = $this->siteModel->getCacheInfo($siteid);
            //if (($logs[$siteid] > 0 && $logs[$siteid] == $this->novel['lastchapterid'])) {
            //    $this->process(sprintf("《%s》站点（ %s - %s ）跳过，本站oid:%s fromoid:%s lastchapterid:%s", $this->novel['name'], $siteInfo['id'], $siteInfo['name'], $logs[$siteid], $fromoid,$this->novel['lastchapterid']));
            //    continue;
            //}
            //要对比的章节
            $data = $this->chapterModel->field('id,oid,name')->where("`novelid`={$this->novel['id']} and `siteid` = {$siteid} and (oid=0 or oid>={$fromoid}) ")->order('id asc')->select();
            $this->process(sprintf("《%s》站点（ %s - %s ）开始重排 章节数 %s", $this->novel['name'], $siteInfo['id'], $siteInfo['name'], count($data)));
            if ($data) {
                //还原
                foreach ($data as $k => $item) {
                    $data[$k]['beforeoid'] = $data[$k]['oid'];
                    $data[$k]['oid']       = 0;
                }
                //重排
                $newdata = $this->compare->order($data, $matchlist, ['name' => $this->novel['name'], 'author' => $this->novel['author']], $siteInfo['isoriginal'] > 0);
                
                //更新
                $max = 0;
                foreach ($newdata as $k => $item) {
                    if ($max < $item['oid']) {
                        $max = $item['oid'];
                    }
                    if ($max > $maxoid) {
                        $matchlist[] = $item;
                        $maxoid      = $max;
                    }
                    if ($item['oid'] != $item['beforeoid']) {
                        $this->chapterModel->where(['id' => $item['id']])->update(['oid' => $item['oid'], 'time' => $_SERVER['REQUEST_TIME']]);
                    }
                }
                $this->logModel->where(['novelid' => $this->novel['id'], 'siteid' => $siteid])->update(['oid' => $max]);
            }
            //$this->process(sprintf("《%s》站点（ %s - %s ）重排完毕", $this->novel['name'], $siteInfo['id'], $siteInfo['name']));
        }
        $this->process(sprintf("《%s》重拍结束", $this->novel['name']));
    }
    
    /**
     * 添加新书
     *
     * @param $fromid
     * @return bool
     */
    protected function _addNewNovel($fromid)
    {
        $result = $this->handler->getInfo($fromid);
        if ($result['status']) {
            $novelname = $result['data']['novelname'];
            if ($this->novel = $this->novelModel->where(['name' => $novelname, 'author' => $result['data']['author']])->find()) {
                //判断这本书站点数量
                if ($num = $this->logModel->where(['novelid' => $this->novel['id']])->count() >= Config::get('collect.max_sourcr', 10)) {
                    $siteInfo = $this->siteModel->getCacheInfo($this->log['siteid']);
                    if ($siteInfo == 0) {
                        //超过10个来源就不增加转载站来源
                        $this->process(sprintf('《%s》已有较多的来源，条狗！当前来源数目:%s', $novelname, $num));
                        return false;
                    }
                }
                
                $this->log['novelid'] = $this->novel['id'];
                //别的站已经添加过了
                if ($this->ruleInfo['newreplace']) {
                    $this->process(sprintf('《%s》已存在，基准站更新为本站', $novelname));
                    $this->novelData = [
                        'siteid'     => $this->ruleInfo['siteid'],
                        'orderid'    => 0,
                        'isover'     => $result['data']['isvoer'],
                        'categoryid' => $result['data']['category_id'],
                        'cover'      => \App\Component\Novel::saveCover($result['data']['cover']),
                        'intro'      => $result['data']['intro'],
                    ];
                } else {
                    $this->process(sprintf('《%s》已存在，关联novelid:%s', $novelname, $this->novel['id']));
                }
            } else {
                //全新的书
                if ($this->ruleInfo['addnew']) {
                    //添加新书
                    $this->process(sprintf('《%s》开始添加新书', $novelname));
                    $novelid = $this->novelModel->addNewNovel([
                        'name'       => $result['data']['novelname'],
                        'author'     => $result['data']['author'],
                        'cover'      => \App\Component\Novel::saveCover($result['data']['cover']),
                        'categoryid' => $result['data']['categoryid'],
                        'intro'      => $result['data']['intro'],
                        'isover'     => $result['data']['isover'],
                        'siteid'     => $this->ruleInfo['siteid'],
                    ]);
                    if ($novelid) {
                        $this->process(sprintf('《%s》添加新书成功,书号：%s', $novelname, $novelid));
                        $this->novel          = $this->novelModel->find($novelid);
                        $this->log['novelid'] = $novelid;
                    } else {
                        $this->process(sprintf('《%s》添加新书失败,错误原因:%s', $novelname, $this->novelModel->getError()));
                        return false;
                    }
                } else {
                    //规则不允许 跳过添加
                    $this->process(sprintf('《%s》不允许添加新书', $novelname));
                    return false;
                }
            }
            return true;
        } else {
            $this->process($result['info'], 'error');
            return false;
        }
    }
    
    /**
     * 更新小说信息
     */
    protected function _updateNovel()
    {
        
        $novelname = $this->novel['name'];
        if (!$novelname) {
            $this->process('没有本站小说信息', 'error');
            return false;
        }
        if ($this->ruleInfo['newreplace'] && $this->novel['siteid'] == '9999') {
            $this->process(sprintf('《%s》siteid为9999 更换基准站', $novelname));
            $this->novel['siteid']     = $this->log['siteid'];
            $this->novel['order']      = 0;
            $this->novelData['siteid'] = $this->log['siteid'];
            $this->novelData['order']  = 0;
        }
        //更新信息
        if ($this->log['lastid'] && $this->log['siteid'] == $this->novel['siteid']) {
            $this->process(sprintf('《%s》基准站采集，更新信息', $novelname));
            //非新书更新信息
            $result = $this->handler->getInfo($this->log['fromid']);
            if ($result['status']) {
                //连载进度
                $this->novelData['isover'] = $result['data']['isover'] ?? 0;
                //封面
                $result['data']['cover'] && $this->novelData['cover'] = \App\Component\Novel::saveCover($result['data']['cover']);
                //简介
                $result['data']['intro'] && $this->novelData['intro'] = $result['data']['intro'];
            } else {
                $this->process($result['info'], 'error');
                return false;
            }
        }
        //获取需要更新的章节数量
        $chapters = $this->_getNewChapters();
        if (!$chapters) {
            $this->process(sprintf('《%s》没有需要采集的章节 跳过', $novelname), 'warning');
            return false;
        }
        //对比并写入章节
        $this->process(sprintf("《%s》需要处理章节数目：%s", $novelname, count($chapters)));
        if ($chapters) {
            $this->process(sprintf("《%s》开始写入章节", $novelname));
            $this->_addNewChapter($chapters);
            $this->process(sprintf("《%s》成功写入章节", $novelname));
            //更新最新章节信息
            $this->log['oid'] = empty($this->log['oid']) ? 0 : $this->log['oid'];
            if (isset($this->log['id'])) {
                $this->logModel->where(['siteid' => $this->log['siteid'], 'fromid' => $this->log['fromid']])->update([
                    'novelid' => $this->log['novelid'],
                    'oid'     => $this->log['oid'],
                    'lastid'  => $this->log['lastsign'],
                    'time'    => time(),
                ]);
            } else {
                $this->logModel->insert([
                    'fromid'  => $this->log['fromid'],
                    'siteid'  => $this->log['siteid'],
                    'novelid' => $this->log['novelid'],
                    'oid'     => $this->log['oid'],
                    'lastid'  => $this->log['lastsign'],
                    'time'    => time(),
                ]);
            }
            
            $this->_updateNovelInfo();
            $this->process(sprintf("《%s》采集小说成功", $novelname), 'success');
            return true;
        } else {
            $this->process(sprintf("《%s》无要处理的内容，跳过", $novelname, count($chapters)), 'warning');
            return false;
        }
    }
    
    /**
     * 更新小说信息
     */
    public function _updateNovelInfo()
    {
        $lastSitechapter = $this->chapterModel->field('siteid,oid,name')->where(['novelid' => $this->novel['id'], 'siteid' => $this->log['siteid']])->order('oid desc')->find();
        $lastChapter     = $this->chapterModel->field('siteid,oid,name')->where(['novelid' => $this->novel['id']])->order('oid desc')->find();
        if ($lastChapter['oid'] == $lastSitechapter['oid']) {
            $this->novelData = array_merge($this->novelData, [
                'lastsiteid'      => $lastSitechapter['siteid'],
                'lastchapterid'   => $lastSitechapter['oid'],
                'lastchaptername' => $lastSitechapter['name'],
                'lastupdate'      => time(),
            ]);
        } elseif ($lastChapter['oid'] != $this->novel['lastchapterid']) {
            $this->novelData = array_merge($this->novelData, [
                'lastsiteid'      => $lastChapter['siteid'],
                'lastchapterid'   => $lastChapter['oid'],
                'lastchaptername' => $lastChapter['name'],
                'lastupdate'      => time(),
            ]);
        } else {
            $this->novelData = array_merge($this->novelData, [
                'lastupdate' => time(),
            ]);
        }
        $this->novelModel->where(['id' => $this->novel['id']])->update($this->novelData);
        if (isset($this->novelData['lastchapterid'])) {
            Mark::I()->where(['novelid' => $this->novel['id'], 'chaptersign' => ['>', $this->novelData['lastchapterid']]])->update(['chaptersign' => $this->novelData['lastchapterid']]);
        }
        
        //清楚缓存
        $this->novelModel->clearCache($this->novel['id']);
        $this->chapterModel->clearCache($this->novel['id'], $this->novelData['lastchapterid'] ?? $this->novel['lastchapterid']);
    }
    
    /**
     * 获取要更新的章节列表
     *
     * @return array|bool
     */
    protected function _getNewChapters()
    {
        $result = $this->handler->getDir($this->log['fromid']);
        if ($result['status']) {
            $return = [];
            //采集过
            $new      = [];
            $chapters = (array)$this->chapterModel->setTableId($this->log['novelid'])->where(['novelid' => $this->log['novelid'], 'siteid' => $this->log['siteid']])->getField('url', true);
            foreach ($chapters as $k => $v) {
                if (substr($v, 0, 4) == 'http') {
                    $chapters[$k] = parse_url($v, PHP_URL_PATH);
                }
            }
            foreach ($result['data'] as $k => $v) {
                if (!in_array($v['chapterurl'], $chapters)) {
                    $new[]      = $v;
                    $chapters[] = $v['chapterurl'];
                }
            }
            foreach ($new as $v) {
                $return[] = [
                    'novelid' => $this->log['novelid'],
                    'siteid'  => $this->log['siteid'],
                    'name'    => $v['chaptername'],
                    'url'     => $v['chapterurl'],
                    'time'    => $_SERVER['REQUEST_TIME'],
                ];
            }
            return $return;
        } else {
            $this->process('获取目录列表失败：' . $result['info'], 'error');
            return false;
        }
    }
    
    /**
     * 添加章节
     *
     * @param $chapters
     * @return null|mixed
     */
    protected function _addNewChapter($chapters)
    {
        if ($this->novel['orderid'] == 0 && $this->novel['lastchapterid'] > 0 && $this->novel['siteid'] < 9999 && $this->novel['siteid'] > 0) {
            //todo 重排
            $this->reorderAll($this->novel);
        }
        $novelid = $this->novel['id'];
        $siteid  = $this->ruleInfo['siteid'];
        
        $this->chapterModel->setTableId($novelid);
        $this->process(sprintf("《%s》开始处理", $this->novel['name']));
        if ($this->novel['siteid'] == $this->log['siteid']) {
            //基准站
            $oid = $lastOid = $this->chapterModel->where(['novelid' => $novelid, 'siteid' => $siteid])->order('oid desc')->getField('oid');
            //基准站 补充oid
            foreach ($chapters as $k => $v) {
                ++$oid;
                $chapters[$k]['oid'] = $oid;
            }
            $this->process(sprintf("《%s》oid计算完毕", $this->novel['name']));
            //插入 重拍其他
            $insertResult = $this->chapterModel->insertAll($chapters);
            
            $this->process(sprintf("《%s》插入章节成功 准备重拍", $this->novel['name']));
            if ($insertResult) {
                $this->novelData['orderid'] = $oid;
                $this->log['oid']           = $oid;
                // 根据之前oid最早的章节开始进行排序
                $this->reOrderChapter($lastOid);
            } else {
                $this->process(sprintf("《%s》写入章节失败，错误原因：%s", $this->novel['name'], $this->chapterModel->getError()), 'error');
                return false;
            }
        } elseif ($this->site['isoriginal'] > 0) {
            //次级基准站+非入库基准站
            //获取当前的源的最新OID
            $lastOid = (int)$this->chapterModel->where(['novelid' => $novelid, 'siteid' => $siteid])->order('id desc')->getField('oid');
            if ($lastOid < $this->novel['lastchapterid']) {
                if ($lastOid > $this->novel['orderid']) {
                    $matchlist = $this->chapterModel->field('name,oid')->where(['novelid' => $novelid, 'oid' => ['>', $lastOid]])->group('oid')->order('oid asc,id asc')->select();
                } else {
                    //次级基准站 新增排序
                    $matchlist = $this->chapterModel->field('name,oid')->where(['novelid' => $novelid, 'siteid' => $this->novel['siteid'], 'oid' => ['>', $lastOid]])->order('oid asc,id asc')->select();
                    // 如果获取到的matchlist 没有达到大的oid 另外获取oid
                    if ($this->novel['orderid'] < $this->novel['lastchapterid']) {
                        $extmatchlist = $this->chapterModel->field('name,oid')->where(['novelid' => $novelid, 'oid' => ['>', $this->novel['orderid']]])->group('oid')->order('oid asc,id asc')->select();
                        $matchlist    = array_merge($matchlist, $extmatchlist);
                    }
                }
                $reOrderData = $this->compare->order($chapters, $matchlist, ['name' => $this->novel['name'], 'author' => $this->novel['author']], true);
            } else {
                //已经为最新的了 那么直接附加处理
                $oid = $lastOid;
                foreach ($chapters as $k => $v) {
                    $oid++;
                    $chapters[$k]['oid'] = $oid;
                }
                $reOrderData = $chapters;
            }
            $res = $this->chapterModel->insertAll($reOrderData);
            if ($res) {
                // 重排 最为复杂的部分重拍 自动纠错 等等
                // 本站点的最大oid
                $maxoid           = $this->chapterModel->where(['novelid' => $novelid, 'siteid' => $siteid])->order('oid desc')->getField('oid');
                $this->log['oid'] = $maxoid;
                if ($this->novel['orderid'] < $maxoid) {
                    //有新章节了 才会启动重拍
                    $this->reOrderChapter($lastOid);
                }
            } else {
                $this->process(sprintf("《%s》写入章节失败，错误原因：%s", $this->novel['name'], $this->chapterModel->getError()), 'error');
                return false;
            }
        } else {
            //普通站点
            $lastOid = (int)$this->chapterModel->where(['novelid' => $novelid, 'siteid' => $siteid])->order('oid desc')->getField('oid');
            //普通站 重排排序
            if ($lastOid < $this->novel['lastchapterid']) {
                $matchlist = $this->chapterModel->field('name,oid')->where(['novelid' => $novelid, 'siteid' => $this->novel['siteid'], 'oid' => ['>', $lastOid]])->order('oid asc,id asc')->select();
                // 如果获取到的matchlist 没有达到大的oid 另外获取oid
                if ($this->novel['orderid'] < $this->novel['lastchapterid']) {
                    $extmatchlist = $this->chapterModel->field('name,oid')->where(['novelid' => $novelid, 'oid' => ['>', $this->novel['orderid']]])->group('oid')->order('oid asc,id asc')->select();
                    $matchlist    = array_merge($matchlist, $extmatchlist);
                }
                $reOrderData = $this->compare->order($chapters, $matchlist, ['name' => $this->novel['name'], 'author' => $this->novel['author']]);
                foreach ($chapters as $k => $item) {
                    $chapters[$k]['oid'] = $reOrderData[$k]['oid'];
                }
            } else {
                foreach ($chapters as $k => $item) {
                    $chapters[$k]['oid'] = 0;
                }
            }
            if ($this->chapterModel->insertAll($chapters)) {
                //从数据库中取是因为可能插入的最后一章没有匹配到oid
                $maxoid           = $this->chapterModel->where(['novelid' => $novelid, 'siteid' => $siteid])->order('oid desc')->getField('oid');
                $this->log['oid'] = $maxoid;
            } else {
                $this->process(sprintf("《%s》写入章节失败，错误原因：%s", $this->novel['name'], $this->chapterModel->getError()), 'error');
                return false;
            }
        }
    }
    
    /**
     * 任务进程id校验
     *
     * @return bool
     */
    protected function checkCronPid()
    {
        static $pid = null;
        if ($pid === null) {
            $pid = time();
            $this->cache->set("cron_pid_{$this->ruleId}", $pid);
            return true;
        } else {
            if ($pid == $this->cache->get("cron_pid_{$this->ruleId}")) {
                return true;
            } else {
                return false;
            }
        }
    }
    
    /**
     * 锁定小说采集状态 防止多个进程同时更新
     *
     * @return mixed
     */
    protected function lockNovel()
    {
        if (DI::Cache()->get("collect_lock_{$this->novel['id']}.time")) {
            $this->process(sprintf('《%s》小说被锁定[由规则“%s”于 %s 开始采集]', $this->novel['name'], $this->cache->get("collect_lock_{$this->novel['id']}.rule"), date('Y-m-d H:i:s', $this->cache->get("collect_lock_{$this->novel['id']}.time"))), 'error');
            return false;
        }
        DI::Cache()->set("collect_lock_{$this->novel['id']}.time", time(), 600);
        DI::Cache()->set("collect_lock_{$this->novel['id']}.rule", $this->ruleName, 600);
        return true;
    }
    
    /**
     * 解除小说锁定状态
     */
    protected function unLockNovel()
    {
        DI::Cache()->remove("collect_lock_{$this->novel['id']}.time");
        DI::Cache()->remove("collect_lock_{$this->novel['id']}.rule");
    }
}