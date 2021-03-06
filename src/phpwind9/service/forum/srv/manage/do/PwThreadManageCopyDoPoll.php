<?php

defined('WEKIT_VERSION') || exit('Forbidden');

/**
 * 帖子复制 - 投票.
 *
 * @author jinlong.panjl <jinlong.panjl@aliyun-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.phpwind.com
 *
 * @version $Id$
 */
class PwThreadManageCopyDoPoll extends PwThreadManageCopyDoBase
{
    protected $attachs = [];

    public function copyThread(PwTopicDm $topicDm, $newTid)
    {
        $special = $topicDm->getField('special');
        if ($special != 1) {
            return;
        }

        return $this->copyPoll($topicDm->tid, $newTid);
    }

    public function initInfo($tid)
    {
        $poll = $pollOption = $pollVoter = [];
        $threadPollInfo = $this->_getThreadPollDs()->getPoll($tid);
        if (empty($threadPollInfo) || !is_array($threadPollInfo)) {
            return [$poll, $pollOption, $pollVoter];
        }

        $pollid = $threadPollInfo['poll_id'];
        $poll = $this->_getPollDs()->getPoll($pollid);
        if (empty($poll) || !is_array($poll)) {
            return [$poll, $pollOption, $pollVoter];
        }

        $pollOption = $this->_getPollOptionDs()->getByPollid($pollid);
        $pollVoter = $this->_getPollVoterDs()->getByPollid($pollid);

        return [$poll, $pollOption, $pollVoter];
    }

    public function copyPoll($tid, $newTid)
    {
        list($poll, $pollOption, $pollVoter) = $this->initInfo($tid);
        if (!$poll) {
            return;
        }

        $pollDm = new PwPollDm(); /* @var $pollDm PwPollDm */
        $pollDm->setVoterNum($poll['voter_num']);
        $pollDm->setIsViewResult($poll['isafter_view']);
        $pollDm->setIsIncludeImg($poll['isinclude_img']);
        $pollDm->setOptionLimit($poll['option_limit']);
        $pollDm->setRegtimeLimit($poll['regtime_limit']);
        $pollDm->setCreatedUserid($poll['created_userid']);
        $pollDm->setAppType($poll['app_type']);
        $pollDm->setExpiredTime($poll['expired_time']);
        $newPollid = $this->_getPollDS()->addPoll($pollDm);

        $optionVoter = [];
        foreach ($pollVoter as $value) {
            $optionVoter[$value['option_id']][] = $value['uid'];
        }

        foreach ($pollOption as $key => $value) {
            $pollOptionDm = new PwPollOptionDm();
            $pollOptionDm->setPollid($newPollid);
            $pollOptionDm->setVotedNum($value['voted_num']);
            $pollOptionDm->setContent($value['content']);
            $pollOptionDm->setImage($value['image']);
            $newOptionid = $this->_getPollOptionDs()->add($pollOptionDm);

            if (isset($optionVoter[$key]) && is_array($optionVoter[$key])) {
                $this->copyVoter($optionVoter[$key], $newPollid, $newOptionid);
            }
        }

        $threadPollDm = new PwThreadPollDm();
        $threadPollDm->setTid($newTid);
        $threadPollDm->setPollid($newPollid);
        $threadPollDm->setCreatedUserid($poll['created_userid']);
        $this->_getThreadPollDs()->addPoll($threadPollDm);

        return true;
    }

    public function copyVoter($uids, $pollid, $optionid)
    {
        if (empty($uids) || !is_array($uids)) {
            return false;
        }

        foreach ($uids as $value) {
            $this->_getPollVoterDs()->add($value, $pollid, $optionid);
        }

        return true;
    }

    /**
     * get PwThreadPoll.
     *
     * @return PwThreadPoll
     */
    private function _getThreadPollDs()
    {
        return Wekit::load('poll.PwThreadPoll');
    }

    /**
     * get PwPoll.
     *
     * @return PwPoll
     */
    private function _getPollDs()
    {
        return Wekit::load('poll.PwPoll');
    }

    /**
     * get PwPollOption.
     *
     * @return PwPollOption
     */
    private function _getPollOptionDs()
    {
        return Wekit::load('poll.PwPollOption');
    }

    /**
     * get PwPollVoter.
     *
     * @return PwPollVoter
     */
    private function _getPollVoterDs()
    {
        return Wekit::load('poll.PwPollVoter');
    }
}
