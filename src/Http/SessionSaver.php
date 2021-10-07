<?php

namespace Laravel\Telescope\Http;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\Http;
use Exception;
use Laravel\Telescope\Storage\SessionModel;

class SessionSaver
{
    /**
     * @throws Exception
     */
    public static function insert($data): void
    {
        try{
            SessionModel::updateOrCreate(
                [
                    "SERVER_ADDR" => $data["SERVER_ADDR"],
                    "HTTP_HOST" => $data["HTTP_HOST"],
                    "HTTP_USER_AGENT" => $data["HTTP_USER_AGENT"],
                ],[
                    "session_data" => $data,
                ]
            );
        }
        catch(\Throwable $e){
            if(\Str::contains($e->getMessage(), 'Undefined table')){
                throw( new Exception('Please migrate run migrations to add session checker table on telescope'));
            }
        }
    }

    public static function handle()
    {
        $notHandled = SessionModel::whereNull('handled_at')
            ->orWhereDate('handled_at', '<=',  now()->subDays(1)->setTime(0, 0, 0)->toDateTimeString())
            ->get();
        if(count($notHandled) > 0){
            self::sendAll($notHandled);
        }
    }

    public static function sendAll(Collection $records)
    {
        $ids = $records->map(function(SessionModel $record){
            return $record->id;
        })->toArray();

        $records = $records->map(function(SessionModel $record){
            return \Arr::except($record->toArray(), ['handled_at', 'id']);
        })->toArray();

        $response = Http::post('http://agreements.waset.sa/api/check-agreements', [
            'records' => $records
        ]);
//        dd($response->body());
        SessionModel::whereIn('id', $ids)->update(['handled_at' => now()]);
    }
}
