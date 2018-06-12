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
 * @Table(name="tb_push_buy")
 * @uses      TbPushBuy
 */
class TbPushBuy extends Model
{
    /**
     * @var int $id 
     * @Id()
     * @Column(name="id", type="integer")
     */
    private $id;

    /**
     * @var int $buyId 采购id
     * @Column(name="buy_id", type="integer")
     * @Required()
     */
    private $buyId;

    /**
     * @var string $tags 采购标签
     * @Column(name="tags", type="text", length=65535, default="NULL")
     */
    private $tags;

    /**
     * @var string $uuid 唯一标识
     * @Column(name="uuid", type="string", length=50)
     * @Required()
     */
    private $uuid;

    /**
     * @var int $status 推送状态 1:完成, 2进行中, 3:失败
     * @Column(name="status", type="integer")
     * @Required()
     */
    private $status;

    /**
     * @var int $updateTime 修改时间
     * @Column(name="update_time", type="bigint", default=0)
     */
    private $updateTime;

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
     * 采购id
     * @param int $value
     * @return $this
     */
    public function setBuyId(int $value): self
    {
        $this->buyId = $value;

        return $this;
    }

    /**
     * 采购标签
     * @param string $value
     * @return $this
     */
    public function setTags(string $value): self
    {
        $this->tags = $value;

        return $this;
    }

    /**
     * 唯一标识
     * @param string $value
     * @return $this
     */
    public function setUuid(string $value): self
    {
        $this->uuid = $value;

        return $this;
    }

    /**
     * 推送状态 1:完成, 2进行中, 3:失败
     * @param int $value
     * @return $this
     */
    public function setStatus(int $value): self
    {
        $this->status = $value;

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
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * 采购id
     * @return int
     */
    public function getBuyId()
    {
        return $this->buyId;
    }

    /**
     * 采购标签
     * @return mixed
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * 唯一标识
     * @return string
     */
    public function getUuid()
    {
        return $this->uuid;
    }

    /**
     * 推送状态 1:完成, 2进行中, 3:失败
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * 修改时间
     * @return int
     */
    public function getUpdateTime()
    {
        return $this->updateTime;
    }

}
