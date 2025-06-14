<?php

namespace WindBox\Infrastructure\Http;

class Response
{
    public function json(array $data, int $statusCode = 200): string
    {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        return json_encode($data);
    }

    public function sendFile(string $filePath, string $mimeType, int $statusCode = 200) //: void
    {  
        if (!file_exists($filePath)) {
            $this->json(['error' => 'File not foundXXXX.'], 404);
            return;
        }

        http_response_code($statusCode);
        header("Content-Type: {$mimeType}");
        readfile($filePath);
    }
}