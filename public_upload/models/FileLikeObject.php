<?php
/**
 * Created by PhpStorm.
 * User: toanle
 * Date: 6/19/18
 * Time: 2:45 PM
 */

namespace public_upload\models;

use \JsonSerializable;

class FileLikeObject implements JsonSerializable {

    private $name;
    private $type;
    private $lastModifiedDate;
    private $size;

    public function __construct($name, $type, $lastModifiedDate, $size) {
        $this->name = $name;
        $this->type = $type;
        $this->lastModifiedDate = $lastModifiedDate;
        $this->size = $size;
    }

    /**
     * @return mixed
     */
    public function getName() {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name) {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getType() {
        return $this->type;
    }

    /**
     * @param mixed $type
     */
    public function setType($type) {
        $this->type = $type;
    }

    /**
     * @return mixed
     */
    public function getLastModifiedDate() {
        return $this->lastModifiedDate;
    }

    /**
     * @param mixed $lastModifiedDate
     */
    public function setLastModifiedDate($lastModifiedDate) {
        $this->lastModifiedDate = $lastModifiedDate;
    }

    /**
     * @return mixed
     */
    public function getSize() {
        return $this->size;
    }

    /**
     * @param mixed $size
     */
    public function setSize($size) {
        $this->size = $size;
    }


    public function jsonSerialize()
    {
        return [
            'name' => $this->name,
            'type' => $this->type,
            'lastModifiedDate' => $this->lastModifiedDate,
            'size' => $this->size
        ];
    }
}