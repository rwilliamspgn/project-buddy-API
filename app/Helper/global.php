<?php

use Hashids\Hashids;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\JsonResponse;

function res($data = null, $msg = 'Success', $code = 200): JsonResponse
{
    return response()->json([
        'code' => $code,
        'msg' => $msg,
        'data' => $data,
    ]);
}

function vRes(Validator $validator): JsonResponse
{
    return res($validator->errors(), 'Validation Failed', 412);
}

function eRes($msg = '', $code = 400): JsonResponse
{
    return res(null, $msg, $code);
}

function encode($id, $connection = 'main'): string
{
    $config = config('hashids.connections.' . $connection);
    return (new Hashids($config['salt'], $config['length'], $config['alphabet']))->encode($id);
}

function decode($hash, $connection = 'main')
{
    $config = config('hashids.connections.' . $connection);
    $decode = (new Hashids($config['salt'], $config['length'], $config['alphabet']))->decode($hash);
    if (count($decode) > 0) return $decode[0];
    return null;
}

function pager($req): array
{
    $page = $req->page ?? 1;
    $limit = $req->limit ?? 10;
    $offset = ($page - 1) * $limit;
    return [
        'offset' => $offset,
        'limit' => $limit,
        'page' => $page,
    ];
}
