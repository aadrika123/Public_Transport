<?php

namespace App\Traits;

use GuzzleHttp\Client;
use GuzzleHttp\Promise;
use Illuminate\Http\Request;

trait GuzzulCinteRequest
{
    public function requestMake(Request $request,string $url)
    {
        $promise="";
        $asyncMethod = in_array($request->getMethod(), ['POST', 'post']) ? 'postAsync' : 'getAsync';
        if ($request->isJson() || !$request->all()) 
        { 
            $promise = (new Client())->$asyncMethod($url ,
                ['json' => $request->all()],
                [
                    'headers' => $request->header()                         // Attach all headers
                ]
            );
        } 
        else 
        {
            $promise = (new Client())->$asyncMethod($url . $request->getRequestUri(), [                // for Multipart
                'multipart' => $this->prepareMultipartData($request),
                [
                    'headers' => $request->header()                         // Attach all headers
                ]
            ]);
        }
        return $promise;
        
    }

    public function send($request)
    {
        $responses = Promise\Utils::settle($request)->wait();
        // Process the response
        $response = $responses[0];
        return [
            "state"=> $response['state'] === Promise\PromiseInterface::FULFILLED,
            "value" => $response['state'] === Promise\PromiseInterface::FULFILLED ? json_decode($response['value']->getBody()->getContents(),true) : [],
            "error" => $response['state'] != Promise\PromiseInterface::FULFILLED?$response['reason']->getMessage():""
        ];
        
    }

    public function prepareMultipartData(Request $req)
    {
        $multipartData = [];

        foreach ($req->all() as $key => $value) {
            $multipartData[] = [
                'name' => $key,
                'contents' => $value,
            ];
        }

        // Add files from the req
        foreach ($req->allFiles() as $key => $file) {
            $multipartData[] = [
                'name' => $key,
                'contents' => fopen($file->getPathname(), 'r'),
                'filename' => $file->getClientOriginalName(),
            ];
        }

        return $multipartData;
    }
}