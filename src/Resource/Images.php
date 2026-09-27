<?php

namespace DashaMail\Resource;

/**
 * Resize (>1600px wide) and recompress an image the same way DashaMail's own
 * file manager does, without changing its format or storing anything.
 * https://dashamail.ru/api/images/
 */
class Images extends AbstractResource
{
    /**
     * Optimize an image already loaded in memory (JSON body, base64-encoded).
     *
     * @param string $binaryData Raw image bytes (JPEG/PNG/GIF)
     * @param array  $params     resize (bool, default true), max_width (int), lossy (bool)
     * @return \DashaMail\Response data.image is the base64-encoded result
     */
    public function optimize($binaryData, array $params = [])
    {
        $params['image'] = base64_encode($binaryData);
        return $this->client->request('POST', '/images/optimize', [], $params);
    }

    /**
     * Optimize an image already sitting on disk, uploaded as multipart/form-data.
     *
     * @param string $filePath Local path to a readable JPEG/PNG/GIF file
     * @param array  $params   resize (bool), max_width (int), lossy (bool)
     * @return \DashaMail\Response data.image is the base64-encoded result
     */
    public function optimizeFile($filePath, array $params = [])
    {
        return $this->client->requestMultipart('POST', '/images/optimize', [], $params, 'file', $filePath);
    }

    /**
     * Same as optimizeFile(), but gets the raw optimized bytes back instead
     * of a base64 JSON payload (?response=binary).
     *
     * @return \DashaMail\BinaryResponse
     */
    public function optimizeFileBinary($filePath, array $params = [])
    {
        return $this->client->requestMultipartBinary('POST', '/images/optimize', ['response' => 'binary'], $params, 'file', $filePath);
    }

    /**
     * Same as optimize(), but gets the raw optimized bytes back instead of a
     * base64 JSON payload (?response=binary).
     *
     * @param string $binaryData Raw image bytes (JPEG/PNG/GIF)
     * @return \DashaMail\BinaryResponse
     */
    public function optimizeBinary($binaryData, array $params = [])
    {
        $params['image'] = base64_encode($binaryData);
        return $this->client->requestBinary('POST', '/images/optimize', ['response' => 'binary'], $params);
    }
}
