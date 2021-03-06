<?php

Wind::import('ADMIN:library.AdminBaseController');

/**
 * 条件扩展-帖子.
 *
 * @author xiaoxia.xu <x_824@sina.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 *
 * @version $Id: TaskConditionBbsController.php 20430 2012-10-29 10:46:33Z xiaoxia.xuxx $
 */
class TaskConditionBbsController extends AdminBaseController
{
    /* (non-PHPdoc)
     * @see AdminBaseController::beforeAction()
     */
    public function beforeAction($handlerAdapter)
    {
        parent::beforeAction($handlerAdapter);
        $var = json_decode(urldecode($this->getInput('var', 'post')), true);
        if (is_array($var)) {
            $condition = [
                'fid' => (int) (isset($var['fid']) ? $var['fid'] : 0),
                'num' => (int) (isset($var['num']) ? $var['num'] : 0),
            ];

            $this->setOutput($condition, 'condition');
        }
    }

    /**
     * 构建版块树.
     *
     * @param int   $parentid
     * @param array $map
     * @param int   $level
     *
     * @return array
     */
    private function _buildForumTree($parentid, $map, $level = '')
    {
        if (!isset($map[$parentid])) {
            return [];
        }
        $array = [];
        foreach ($map[$parentid] as $key => $value) {
            $value['level'] = $level;
            $value['name'] = strip_tags($value['name']);
            $array[] = $value;
            $array = array_merge($array, $this->_buildForumTree($value['fid'], $map, $level.'&nbsp;&nbsp;'));
        }

        return $array;
    }

    /**
     * 获得版块列表.
     */
    private function _getForumList()
    {
        /* @var $forumSrv PwForumService */
        $forumSrv = Wekit::load('forum.srv.PwForumService');
        $map = $forumSrv->getForumMap();
        $catedb = $map[0];
        foreach ($catedb as $_k => $_v) {
            $catedb[$_k]['name'] = strip_tags($_v['name']);
        }
        $forumList = [];
        foreach ($catedb as $value) {
            $forumList[$value['fid']] = $this->_buildForumTree($value['fid'], $map);
        }
        $this->setOutput($catedb, 'catedb');
        $this->setOutput($forumList, 'forumList');
    }

    /* (non-PHPdoc)
     * @see WindController::run()
     */
    public function run()
    {
        $this->_getForumList();
        $this->setTemplate('condition.bbs_post');
    }

    /**
     * 回帖.
     */
    public function replyAction()
    {
        $this->setTemplate('condition.bbs_reply');
    }

    /**
     * 喜欢帖子.
     */
    public function likeAction()
    {
        $this->_getForumList();
        $this->setTemplate('condition.bbs_like');
    }
}
