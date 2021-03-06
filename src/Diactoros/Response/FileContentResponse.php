<?php
namespace Staticus\Diactoros\Response;

use Psr\Http\Message\StreamInterface;
use Zend\Diactoros\Response;
use Zend\Diactoros\Stream;

/**
 * A class representing HTTP responses with body.
 */
class FileContentResponse extends Response implements FileResponseInterface
{
    use Response\InjectContentTypeTrait;
    /**
     * @var resource
     */
    protected $resource;

    /**
     * @var string
     */
    protected $content;

    /**
     * @return resource
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @return string
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param mixed $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * Create an empty response with the given status code.
     *
     * @param string|resource|Stream $content
     * @param int $status Status code for the response, if any.
     * @param array $headers Headers for the response, if any.
     */
    public function __construct($content = null, $status = 204, array $headers = [])
    {
        $body = $this->createBody($content);
        parent::__construct($body, $status, $headers);
    }

    protected function createBody($content)
    {
        if (is_resource($content)) {
            $this->resource = $content;
            $content = null;
        }
        $this->content = $content;
        if ($content instanceof StreamInterface) {

            return $content;
        } elseif (null === $content || false === $content || '' === $content) { // but not zero
            $stream = new Stream('php://temp', 'r');

            return $stream;
        }
        $stream = new Stream('php://temp', 'wb+');
        $stream->write((string)$content);
        $stream->rewind();

        return $stream;
    }
}
