<?php


/**
 * 上传头像的扩展.
 *
 * @author Shi Long <long.shi@alibaba-inc.com>
 * @copyright ©2003-2103 phpwind.com
 * @license http://www.windframework.com
 *
 * @version $Id: PwTaskMemberAvatarDo.php 18618 2012-09-24 09:31:00Z jieyin $
 */
class PwTaskMemberAvatarDo implements PwTaskCompleteInterface
{
    /**
     * 更新用户头像.
     *
     * @param int $uid
     *
     * @return bool
     */
    public function uploadAvatar($uid)
    {
        if (!Wekit::C('site', 'task.isOpen')) {
            return true;
        }
        $bp = new PwTaskComplete($uid, $this);
        $bp->doTask('member', 'avatar');
    }

    /* (non-PHPdoc)
     * @see PwTaskCompleteInterface::doTask()
     */
    public function doTask($conditions, $step)
    {
        $step['percent'] = '100%';

        return ['isComplete' => true, 'step' => $step];
    }
}
