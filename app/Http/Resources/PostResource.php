<?php
// app/Http/Resources/SuccessResource.php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;



class PostResource extends JsonResource
{
    protected $message;
    protected $status;

    public function __construct($resource, $message, $status)
    {
        parent::__construct($resource);
        $this->message = $message;
        $this->status = $status;
    }

    public function toResponse($request)
    {
        $response = [
            'message' => $this->message,
            'status' => $this->status,
            'data' => $this->resource,
        ];

        return response()->json(['response' => $response]);
    }
}
