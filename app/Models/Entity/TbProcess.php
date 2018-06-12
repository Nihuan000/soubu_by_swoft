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
 * @Entity()
 * @Table(name="tb_process")
 * @uses      TbProcess
 */
class TbProcess extends Model
{
    /**
     * @var int $id 
     * @Id()
     * @Column(name="id", type="integer")
     */
    private $id;

    /**
     * @var int $o2cId 如果是增量信息, 则存放增量id, 否则null
     * @Column(name="o2c_id", type="integer", default=0)
     */
    private $o2cId;

    /**
     * @var string $uuid 当前任务对应的uuid
     * @Column(name="uuid", type="string", length=32)
     * @Required()
     */
    private $uuid;

    /**
     * @var int $type 类型  增量索引:0x100下面        快照变更:0x200 下面 
     * @Column(name="type", type="integer")
     * @Required()
     */
    private $type;

    /**
     * @var string $detail 进程详细信息 使用json存放 对应索引变更/镜像变更 等信息
     * @Column(name="detail", type="text", length=65535, default="NULL")
     */
    private $detail;

    /**
     * @var int $startTime 开始时间
     * @Column(name="start_time", type="bigint", default=0)
     */
    private $startTime;

    /**
     * @var int $endTime 结束时间
     * @Column(name="end_time", type="bigint", default=0)
     */
    private $endTime;

    /**
     * @var int $state 任务状态  1:进行中    0:结束    2:失败
     * @Column(name="state", type="integer", default=1)
     */
    private $state;

    /**
     * @var string $failure 如果任务执行失败 存放失败信息
     * @Column(name="failure", type="text", length=65535, default="NULL")
     */
    private $failure;

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
     * 如果是增量信息, 则存放增量id, 否则null
     * @param int $value
     * @return $this
     */
    public function setO2cId(int $value): self
    {
        $this->o2cId = $value;

        return $this;
    }

    /**
     * 当前任务对应的uuid
     * @param string $value
     * @return $this
     */
    public function setUuid(string $value): self
    {
        $this->uuid = $value;

        return $this;
    }

    /**
     * 类型  增量索引:0x100下面        快照变更:0x200 下面 
     * @param int $value
     * @return $this
     */
    public function setType(int $value): self
    {
        $this->type = $value;

        return $this;
    }

    /**
     * 进程详细信息 使用json存放 对应索引变更/镜像变更 等信息
     * @param string $value
     * @return $this
     */
    public function setDetail(string $value): self
    {
        $this->detail = $value;

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
     * 任务状态  1:进行中    0:结束    2:失败
     * @param int $value
     * @return $this
     */
    public function setState(int $value): self
    {
        $this->state = $value;

        return $this;
    }

    /**
     * 如果任务执行失败 存放失败信息
     * @param string $value
     * @return $this
     */
    public function setFailure(string $value): self
    {
        $this->failure = $value;

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
     * 如果是增量信息, 则存放增量id, 否则null
     * @return mixed
     */
    public function getO2cId()
    {
        return $this->o2cId;
    }

    /**
     * 当前任务对应的uuid
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * 类型  增量索引:0x100下面        快照变更:0x200 下面 
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * 进程详细信息 使用json存放 对应索引变更/镜像变更 等信息
     * @return mixed
     */
    public function getDetail()
    {
        return $this->detail;
    }

    /**
     * 开始时间
     * @return mixed
     */
    public function getStartTime()
    {
        return $this->startTime;
    }

    /**
     * 结束时间
     * @return mixed
     */
    public function getEndTime()
    {
        return $this->endTime;
    }

    /**
     * 任务状态  1:进行中    0:结束    2:失败
     * @return mixed
     */
    public function getState()
    {
        return $this->state;
    }

    /**
     * 如果任务执行失败 存放失败信息
     * @return mixed
     */
    public function getFailure()
    {
        return $this->failure;
    }

}
