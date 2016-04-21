<?php
namespace Staticus\Resources\Image;
use Staticus\Resources\ResourceDOAbstract;
use Staticus\Resources\Image\ResourceImageDOInterface;

/**
 * Domain Object
 * @package Staticus\Resources\File
 */
abstract class ResourceImageDO extends ResourceDOAbstract implements ResourceImageDOInterface
{
    const DEFAULT_WIDTH = 0;
    const DEFAULT_HEIGHT = 0;
    const DEFAULT_SIZE = '0';
    protected $width = 0;
    protected $height = 0;

    /**
     * @var CropImageDOInterface
     */
    protected $crop = null;

    public function reset()
    {
        parent::reset();
        $this->type = static::TYPE;
        $this->width = 0;
        $this->height = 0;
        return $this;
    }

    /**
     * You can't change the concrete ImageType
     * @param string $type
     * @return $this
     */
    public function setType($type)
    {
        return $this;
    }

    /**
     * @return int
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * @param mixed $width
     * @return ResourceImageDO
     */
    public function setWidth($width = self::DEFAULT_WIDTH)
    {
        $this->width = (int)$width;
        $this->setFilePath();

        return $this;
    }

    /**
     * @return int
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * @param mixed $height
     * @return ResourceImageDO
     */
    public function setHeight($height = self::DEFAULT_HEIGHT)
    {
        $this->height = (int)$height;
        $this->setFilePath();

        return $this;
    }

    protected function setFilePath()
    {
        $this->filePath = $this->generateFilePath();
    }

    /**
     * /type/variant/version/[size/][other-type-specified/]uuid.type
     * /jpg/default/0/0/22af64.jpg
     * /jpg/user1534/3/0/22af64.jpg
     * /jpg/fractal/0/30x40/22af64.jpg
     */
    public function generateFilePath()
    {
        return $this->getBaseDirectory()
            . $this->getType() . DIRECTORY_SEPARATOR
            . $this->getVariant() . DIRECTORY_SEPARATOR
            . $this->getVersion() . DIRECTORY_SEPARATOR
            . $this->getSize() . DIRECTORY_SEPARATOR
            . $this->getUuid() . '.' . $this->getType();
    }

    /**
     * @return int|string
     */
    public function getSize()
    {
        $width = $this->getWidth();
        $height = $this->getHeight();
        $size = ($width > 0 && $height > 0)
            ? $width . 'x' . $height
            : self::DEFAULT_SIZE;

        return $size;
    }

    /**
     * @return CropImageDOInterface|null
     */
    public function getCrop()
    {
        return $this->crop;
    }

    /**
     * @param CropImageDOInterface $crop
     * @return ResourceImageDO
     */
    public function setCrop(CropImageDOInterface $crop = null)
    {
        $this->crop = $crop;
        return $this;
    }

    public function toArray()
    {
        $data = parent::toArray();
        if ($this->crop) {
            $data['crop'] = $this->crop->toArray();
        }

        return $data;
    }

}