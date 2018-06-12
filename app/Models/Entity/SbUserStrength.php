<?php
namespace App\Models\Entity;

use Swoft\Db\Model;
use Swoft\Db\Bean\Annotation\Column;
use Swoft\Db\Bean\Annotation\Entity;
use Swoft\Db\Bean\Annotation\Id;
use Swoft\Db\Bean\Annotation\Required;
use Swoft\Db\Bean\Annotation\Table;
use Swoft\Db\Types;

/**
 * 实力商家表

 * @Entity()
 * @Table(name="sb_user_strength")
 * @uses      SbUserStrength
 */
class SbUserStrength extends Model
{
    /**
     * @var int $id 
     * @Id()
     * @Column(name="id", type="integer")
     */
    private $id;

    /**
     * @var int $userId 用户id
     * @Column(name="user_id", type="integer")
     * @Required()
     */
    private $userId;

    /**
     * @var int $startTime 开始时间
     * @Column(name="start_time", type="integer")
     * @Required()
     */
    private $startTime;

    /**
     * @var int $endTime 结束时间
     * @Column(name="end_time", type="integer")
     * @Required()
     */
    private $endTime;

    /**
     * @var int $isExpire 是否过期
     * @Column(name="is_expire", type="tinyint", default=0)
     */
    private $isExpire;

    /**
     * @var float $serviceFeeRate 服务费比例 0-1之间 0:不收费服务费 0.1:10%的服务费 1:100%的服务费
     * @Column(name="service_fee_rate", type="float")
     * @Required()
     */
    private $serviceFeeRate;

    /**
     * @var int $level 实力商家等级 5:1888充值的实力商家 10:4888充值的实力商家
     * @Column(name="level", type="smallint")
     * @Required()
     */
    private $level;

    /**
     * @var int $addTime 添加时间
     * @Column(name="add_time", type="integer")
     * @Required()
     */
    private $addTime;

    /**
     * @var int $updateTime 修改时间
     * @Column(name="update_time", type="integer")
     * @Required()
     */
    private $updateTime;

    /**
     * @var string $remark 备注
     * @Column(name="remark", type="string", length=255, default="''")
     */
    private $remark;

    /**
     * @param int $value
     * @return $this
     */
    public function setId(int $value)
    {
        $this->id = $value;

        return $this;
    }

    /**
     * 用户id
     * @param int $value
     * @return $this
     */
    public function setUserId(int $value): self
    {
        $this->userId = $value;

        return $this;
    }

    /**
     * 开始时间
     * @param int $value
     * @return $this
     */
    public function setStartTime(int $value): self
    {
        $this->startTime = $value;

        return $this;
    }

    /**
     * 结束时间
     * @param int $value
     * @return $this
     */
    public function setEndTime(int $value): self
    {
        $this->endTime = $value;

        return $this;
    }

    /**
     * 是否过期
     * @param int $value
     * @return $this
     */
    public function setIsExpire(int $value): self
    {
        $this->isExpire = $value;

        return $this;
    }

    /**
     * 服务费比例 0-1之间 0:不收费服务费 0.1:10%的服务费 1:100%的服务费
     * @param float $value
     * @return $this
     */
    public function setServiceFeeRate(float $value): self
    {
        $this->serviceFeeRate = $value;

        return $this;
    }

    /**
     * 实力商家等级 5:1888充值的实力商家 10:4888充值的实力商家
     * @param int $value
     * @return $this
     */
    public function setLevel(int $value): self
    {
        $this->level = $value;

        return $this;
    }

    /**
     * 添加时间
     * @param int $value
     * @return $this
     */
    public function setAddTime(int $value): self
    {
        $this->addTime = $value;

        return $this;
    }

    /**
     * 修改时间
     * @param int $value
     * @return $this
     */
    public function setUpdateTime(int $value): self
    {
        $this->updateTime = $value;

        return $this;
    }

    /**
     * 备注
     * @param string $value
     * @return $this
     */
    public function setRemark(string $value): self
    {
        $this->remark = $value;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * 用户id
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * 开始时间
     * @return int
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * 结束时间
     * @return int
     */
    public function getEndTime()
    {
        return $this->endTime;
    }

    /**
     * 是否过期
     * @return int
     */
    public function getIsExpire()
    {
        return $this->isExpire;
    }

    /**
     * 服务费比例 0-1之间 0:不收费服务费 0.1:10%的服务费 1:100%的服务费
     * @return float
     */
    public function getServiceFeeRate()
    {
        return $this->serviceFeeRate;
    }

    /**
     * 实力商家等级 5:1888充值的实力商家 10:4888充值的实力商家
     * @return int
     */
    public function getLevel()
    {
        return $this->level;
    }

    /**
     * 添加时间
     * @return int
     */
    public function getAddTime()
    {
        return $this->addTime;
    }

    /**
     * 修改时间
     * @return int
     */
    public function getUpdateTime()
    {
        return $this->updateTime;
    }

    /**
     * 备注
     * @return mixed
     */
    public function getRemark()
    {
        return $this->remark;
    }

}
