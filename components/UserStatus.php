<?php
/**
 * @Author: Wang chunsheng  email:2192138785@qq.com
 * @Date:   2022-06-18 10:25:14
 * @Last Modified by:   Wang chunsheng  email:2192138785@qq.com
 * @Last Modified time: 2022-10-22 12:09:21
 */

namespace diandi\admin\components;

/**
 * Description of UserStatus.
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 *
 * @since 2.9
 */
class UserStatus
{
    // 待审核
    const INACTIVE = 0;
    // 审核
    const ACTIVE = 1;
    // 体验期
    const experience = 2;
    // 到期
    const endtime = 3;
}
