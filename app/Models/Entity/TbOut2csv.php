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
 * @Table(name="tb_out2csv")
 * @uses      TbOut2csv
 */
class TbOut2csv extends Model
{
    /**
     * @var int $id 
     * @Id()
     * @Column(name="id", type="integer")
     */
    private $id;

    /**
     * @var string $out2csvId 源数据转换为中间数据的过程id
     * @Column(name="out2csv_id", type="string", length=255)
     * @Required()
     */
    private $out2csvId;

    /**
     * @var string $parameter 使用json存放参数信息
     * @Column(name="parameter", type="text", length=65535, default="NULL")
     */
    private $parameter;

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
     * 源数据转换为中间数据的过程id
     * @param string $value
     * @return $this
     */
    public function setOut2csvId(string $value): self
    {
        $this->out2csvId = $value;

        return $this;
    }

    /**
     * 使用json存放参数信息
     * @param string $value
     * @return $this
     */
    public function setParameter(string $value): self
    {
        $this->parameter = $value;

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
     * 源数据转换为中间数据的过程id
     * @return string
     */
    public function getOut2csvId()
    {
        return $this->out2csvId;
    }

    /**
     * 使用json存放参数信息
     * @return mixed
     */
    public function getParameter()
    {
        return $this->parameter;
    }

}
