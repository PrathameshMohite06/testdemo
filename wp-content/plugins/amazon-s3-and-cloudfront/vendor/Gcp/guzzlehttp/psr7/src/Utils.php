<?php

namespace DeliciousBrains\WP_Offload_Media\Gcp\GuzzleHttp\Psr7;

use DeliciousBrains\WP_Offload_Media\Gcp\Psr\Http\Message\RequestInterface;
use DeliciousBrains\WP_Offload_Media\Gcp\Psr\Http\Message\ServerRequestInterface;
use DeliciousBrains\WP_Offload_Media\Gcp\Psr\Http\Message\StreamInterface;
use DeliciousBrains\WP_Offload_Media\Gcp\Psr\Http\Message\UriInterface;
final class Utils
{
    /**
     * Remove the items given by the keys, case insensitively from the data.
     *
     * @param iterable<string> $keys
     *
     * @return array
     */
    public static function caselessRemove($keys, array $data)
    {
        $result = [];
        foreach ($keys as &$key) {
            $key = strtolower($key);
        }
        foreach ($data as $k => $v) {
            if (!in_array(strtolower($k), $keys)) {
                $result[$k] = $v;
            }
        }
        return $result;
    }
    /**
     * Copy the contents of a stream into another stream until the given number
     * of bytes have been read.
     *
     * @param StreamInterface $source Stream to read from
     * @param StreamInterface $dest   Stream to write to
     * @param int             $maxLen Maximum number of bytes to read. Pass -1
     *                                to read the entire stream.
     *
     * @throws \RuntimeException on error.
     */
    public static function copyToStream(\DeliciousBrains\WP_Offload_Media\Gcp\Psr\Http\Message\StreamInterface $source, \DeliciousBrains\WP_Offload_Media\Gcp\Psr\Http\Message\StreamInterface $dest, $maxLen = -1)
    {
        $bufferSize = 8192;
        if ($maxLen === -1) {
            while (!$source->eof()) {
                if (!$dest->write($source->read($bufferSize))) {
                    break;
                }
            }
        } else {
            $remaining = $maxLen;
            while ($remaining > 0 && !$source->eof()) {
                $buf = $source->read(min($bufferSize, $remaining));
                $len = strlen($buf);
                if (!$len) {
                    break;
                }
                $remaining -= $len;
                $dest->write($buf);
            }
        }
    }
    /**
     * Copy the contents of a stream into a string until the given number of
     * bytes have been read.
     *
     * @param StreamInterface $stream Stream to read
     * @param int             $maxLen Maximum number of bytes to read. Pass -1
     *                                to read the entire stream.
     * @return string
     *
     * @throws \RuntimeException on error.
     */
    public static function copyToString(\DeliciousBrains\WP_Offload_Media\Gcp\Psr\Http\Message\StreamInterface $stream, $maxLen = -1)
    {
        $buffer = '';
        if ($maxLen === -1) {
            while (!$stream->eof()) {
                $buf = $stream->read(1048576);
                // Using a loose equality here to match on '' and false.
                if ($buf == null) {
                    break;
                }
                $buffer .= $buf;
            }
            return $buffer;
        }
        $len = 0;
        while (!$stream->eof() && $len < $maxLen) {
            $buf = $stream->read($maxLen - $len);
            // Using a loose equality here to match on '' and false.
            if ($buf == null) {
                break;
            }
            $buffer .= $buf;
            $len = strlen($buffer);
        }
        return $buffer;
    }
    /**
     * Calculate a hash of a stream.
     *
     * This method reads the entire stream to calculate a rolling hash, based
     * on PHP's `hash_init` functions.
     *
     * @param StreamInterface $stream    Stream to calculate the hash for
     * @param string          $algo      Hash algorithm (e.g. md5, crc32, etc)
     * @param bool            $rawOutput Whether or not to use raw output
     *
     * @return string Returns the hash of the stream
     *
     * @throws \RuntimeException on error.
     */
    public static function hash(\DeliciousBrains\WP_Offload_Media\Gcp\Psr\Http\Message\StreamInterface $stream, $algo, $rawOutput = false)
    {
        $pos = $stream->tell();
        if ($pos > 0) {
            $stream->rewind();
        }
        $ctx = hash_init($algo);
        while (!$stream->eof()) {
            hash_update($ctx, $stream->read(1048576));
        }
        $out = hash_final($ctx, (bool) $rawOutput);
        $stream->seek($pos);
        return $out;
    }
    /**
     * Clone and modify a request with the given changes.
     *
     * This method is useful for reducing the number of clones needed to mutate
     * a message.
     *
     * The changes can be one of:
     * - method: (string) Changes the HTTP method.
     * - set_headers: (array) Sets the given headers.
     * - remove_headers: (array) Remove the given headers.
     * - body: (mixed) Sets the given body.
     * - uri: (UriInterface) Set the URI.
     * - query: (string) Set the query string value of the URI.
     * - version: (string) Set the protocol version.
     *
     * @param RequestInterface $request Request to clone and modify.
     * @param array            $changes Changes to apply.
     *
     * @return RequestInterface
     */
    public static function modifyRequest(\DeliciousBrains\WP_Offload_Media\Gcp\Psr\Http\Message\RequestInterface $request, array $changes)
    {
        if (!$changes) {
            return $request;
        }
        $headers = $request->getHeaders();
        if (!isset($changes['uri'])) {
            $uri = $request->getUri();
        } else {
            // Remove the host header if one is on the URI
            if ($host = $changes['uri']->getHost()) {
                $changes['set_headers']['Host'] = $host;
                if ($port = $changes['uri']->getPort()) {
                    $standardPorts = ['http' => 80, 'https' => 443];
                    $scheme = $changes['uri']->getScheme();
                    if (isset($standardPorts[$scheme]) && $port != $standardPorts[$scheme]) {
                        $changes['set_headers']['Host'] .= ':' . $port;
                    }
                }
            }
            $uri = $changes['uri'];
        }
        if (!empty($changes['remove_headers'])) {
            $headers = self::caselessRemove($changes['remove_headers'], $headers);
        }
        if (!empty($changes['set_headers'])) {
            $headers = self::caselessRemove(array_keys($changes['set_headers']), $headers);
            $headers = $changes['set_headers'] + $headers;
        }
        if (isset($changes['query'])) {
            $uri = $uri->withQuery($changes['query']);
        }
        if ($request instanceof ServerRequestInterface) {
            return (new \DeliciousBrains\WP_Offload_Media\Gcp\GuzzleHttp\Psr7\ServerRequest(isset($changes['method']) ? $changes['method'] : $request->getMethod(), $uri, $headers, isset($changes['body']) ? $changes['body'] : $request->getBody(), isset($changes['version']) ? $changes['version'] : $request->getProtocolVersion(), $request->getServerParams()))->withParsedBody($request->getParsedBody())->withQueryParams($request->getQueryParams())->withCookieParams($request->getCookieParams())->withUploadedFiles($request->getUploadedFiles());
        }
        return new \DeliciousBrains\WP_Offload_Media\Gcp\GuzzleHttp\Psr7\Request(isset($changes['method']) ? $changes['method'] : $request->getMethod(), $uri, $headers, isset($changes['body']) ? $changes['body'] : $request->getBody(), isset($changes['version']) ? $changes['version'] : $request->getProtocolVersion());
    }
    /**
     * Read a line from the stream up to the maximum allowed buffer length.
     *
     * @param StreamInterface $stream    Stream to read from
     * @param int|null        $maxLength Maximum buffer length
     *
     * @return string
     */
    public static function readLine(\DeliciousBrains\WP_Offload_Media\Gcp\Psr\Http\Message\StreamInterface $stream, $maxLength = null)
    {
        $buffer = '';
        $size = 0;
        while (!$stream->eof()) {
            // Using a loose equality here to match on '' and false.
            if (null == ($byte = $stream->read(1))) {
                return $buffer;
            }
            $buffer .= $byte;
            // Break when a new line is found or the max length - 1 is reached
            if ($byte === "\n" || ++$size === $maxLength - 1) {
                break;
            }
        }
        return $buffer;
    }
    /**
     * Create a new stream based on the input type.
     *
     * Options is an associative array that can contain the following keys:
     * - metadata: Array of custom metadata.
     * - size: Size of the stream.
     *
     * This method accepts the following `$resource` types:
     * - `Psr\Http\Message\StreamInterface`: Returns the value as-is.
     * - `string`: Creates a stream object that uses the given string as the contents.
     * - `resource`: Creates a stream object that wraps the given PHP stream resource.
     * - `Iterator`: If the provided value implements `Iterator`, then a read-only
     *   stream object will be created that wraps the given iterable. Each time the
     *   stream is read from, data from the iterator will fill a buffer and will be
     *   continuously called until the buffer is equal to the requested read size.
     *   Subsequent read calls will first read from the buffer and then call `next`
     *   on the underlying iterator until it is exhausted.
     * - `object` with `__toString()`: If the object has the `__toString()` method,
     *   the object will be cast to a string and then a stream will be returned that
     *   uses the string value.
     * - `NULL`: When `null` is passed, an empty stream object is returned.
     * - `callable` When a callable is passed, a read-only stream object will be
     *   created that invokes the given callable. The callable is invoked with the
     *   number of suggested bytes to read. The callable can return any number of
     *   bytes, but MUST return `false` when there is no more data to return. The
     *   stream object that wraps the callable will invoke the callable until the
     *   number of requested bytes are available. Any additional bytes will be
     *   buffered and used in subsequent reads.
     *
     * @param resource|string|null|int|float|bool|StreamInterface|callable|\Iterator $resource Entity body data
     * @param array                                                                  $options  Additional options
     *
     * @return StreamInterface
     *
     * @throws \InvalidArgumentException if the $resource arg is not valid.
     */
    public static function streamFor($resource = '', array $options = [])
    {
        if (is_scalar($resource)) {
            $stream = fopen('php://temp', 'r+');
            if ($resource !== '') {
                fwrite($stream, $resource);
                fseek($stream, 0);
            }
            return new \DeliciousBrains\WP_Offload_Media\Gcp\GuzzleHttp\Psr7\Stream($stream, $options);
        }
        switch (gettype($resource)) {
            case 'resource':
                return new \DeliciousBrains\WP_Offload_Media\Gcp\GuzzleHttp\Psr7\Stream($resource, $options);
            case 'object':
                if ($resource instanceof StreamInterface) {
                    return $resource;
                } elseif ($resource instanceof \Iterator) {
                    return new \DeliciousBrains\WP_Offload_Media\Gcp\GuzzleHttp\Psr7\PumpStream(function () use($resource) {
                        if (!$resource->valid()) {
                            return false;
                        }
                        $result = $resource->current();
                        $resource->next();
                        return $result;
                    }, $options);
                } elseif (method_exists($resource, '__toString')) {
                    return \DeliciousBrains\WP_Offload_Media\Gcp\GuzzleHttp\Psr7\Utils::streamFor((string) $resource, $options);
                }
                break;
            case 'NULL':
                return new \DeliciousBrains\WP_Offload_Media\Gcp\GuzzleHttp\Psr7\Stream(fopen('php://temp', 'r+'), $options);
        }
        if (is_callable($resource)) {
            return new \DeliciousBrains\WP_Offload_Media\Gcp\GuzzleHttp\Psr7\PumpStream($resource, $options);
        }
        throw new \InvalidArgumentException('Invalid resource type: ' . gettype($resource));
    }
    /**
     * Safely opens a PHP stream resource using a filename.
     *
     * When fopen fails, PHP normally raises a warning. This function adds an
     * error handler that checks for errors and throws an exception instead.
     *
     * @param string $filename File to open
     * @param string $mode     Mode used to open the file
     *
     * @return resource
     *
     * @throws \RuntimeException if the file cannot be opened
     */
    public static function tryFopen($filename, $mode)
    {
        $ex = null;
        set_error_handler(function () use($filename, $mode, &$ex) {
            $ex = new \RuntimeException(sprintf('Unable to open %s using mode %s: %s', $filename, $mode, func_get_args()[1]));
        });
        $handle = fopen($filename, $mode);
        restore_error_handler();
        if ($ex) {
            /** @var $ex \RuntimeException */
            throw $ex;
        }
        return $handle;
    }
    /**
     * Returns a UriInterface for the given value.
     *
     * This function accepts a string or UriInterface and returns a
     * UriInterface for the given value. If the value is already a
     * UriInterface, it is returned as-is.
     *
     * @param string|UriInterface $uri
     *
     * @return UriInterface
     *
     * @throws \InvalidArgumentException
     */
    public static function uriFor($uri)
    {
        if ($uri instanceof UriInterface) {
            return $uri;
        }
        if (is_string($uri)) {
            return new \DeliciousBrains\WP_Offload_Media\Gcp\GuzzleHttp\Psr7\Uri($uri);
        }
        throw new \InvalidArgumentException('URI must be a string or UriInterface');
    }
}