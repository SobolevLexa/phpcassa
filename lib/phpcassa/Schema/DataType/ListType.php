<?php
namespace phpcassa\Schema\DataType;

/**
 * Handles any type of List.
 *
 * @package phpcassa\Schema\DataType
 */
class ListType extends CassandraType
{
    protected $valueType;

    public function __construct(CassandraType $valueType)
    {
        $this->valueType = $valueType;
    }

    private function readUInt16BE($value,$offset)
    {
        list(, $int) = unpack('s*', strrev(substr($value,$offset,2)));
        return $int;
    }

    private function writeUInt16BE($value)
    {
        return strrev(pack('s*', $value));
    }

    public function pack($data, $is_name = null, $slice_end = null, $is_data = null)
    {
        $return = $this->writeUInt16BE(count($data));

        foreach ($data as $value){
            $packed = $this->valueType->pack($value);
            $return.= $this->writeUInt16BE(strlen($packed));
            $return.= $packed;
        }

        return $return;
    }

    public function unpack($data, $is_name = null)
    {
        $items = [];
        if ($data) {
        $offset = 0;
        $total = $this->readUInt16BE($data,$offset);
        $offset += 2;

        for ($i = 0;$i < $total;$i++){
            $length = $this->readUInt16BE($data,$offset);
            $offset += 2;
            $items[] = $this->valueType->unpack(substr($data,$offset,$length));
            $offset += $length;
            }
        }

        return $items;
    }
}
