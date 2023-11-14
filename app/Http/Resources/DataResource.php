<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DataResource extends JsonResource
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
