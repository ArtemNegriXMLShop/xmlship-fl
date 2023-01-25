<?php

namespace App\Foundation\Laravel\Responses;

use Illuminate\Contracts\Support\Responsable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

readonly class ErrorResponse implements Responsable
{
    public function __construct(
        private string $message,
        private ?Throwable $exception = null,
        private int $code = Response::HTTP_INTERNAL_SERVER_ERROR,
        private array $headers = []
    ) {
    }

    /**
     * @param $request
     * @return JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function toResponse($request): JsonResponse|\Symfony\Component\HttpFoundation\Response
    {
        $response = ['message' => $this->message];

        if (!is_null($this->exception)) {
            $debug = [
                'controller_message' => $this->message,
                'message' => $this->exception->getMessage(),
                'file' => $this->exception->getFile(),
                'line' => $this->exception->getLine(),
                'user' => Auth::id(),
                'trace' => $this->getTrace($this->exception),
            ];

            Log::error(
                trim(trim(str_replace(base_path(), '', $this->exception->getFile()), '/')),
                $debug
            );

            if (config('app.debug')) {
                $response['debug'] = $debug;
            }
        }

        return response()->json($response, $this->code, $this->headers);
    }

    private function getTrace(?Throwable $exception = null): array
    {
        if (null === $exception) {
            return [];
        }

        $trace = [];
        foreach ($exception->getTrace() as $item) {
            if (Arr::exists($item, 'class') && preg_replace(
                    '~\\+~',
                    '\\',
                    $item['class']
                ) == 'Illuminate\Routing\Controller') {
                break;
            }

            if (Arr::exists($item, 'args')) {
                $args = [];
                foreach ($item['args'] as $macro_value) {
                    if(!is_array($macro_value)) {
                        continue;
                    }
                    foreach ($macro_value as $type => $value) {
                        if ($type !== 'server' && gettype($value) === 'object' && method_exists($value, 'all')) {
                            $args[$type] = $value->all();
                            if (empty($args[$type])) {
                                unset($args[$type]);
                            }
                            if ($type === 'headers') {
                                $args[$type] = Arr::except(
                                    $args['headers'],
                                    [
                                        'connection',
                                        'accept-encoding',
                                        'postman-token',
                                        'accept',
                                        'user-agent',
                                        'content-type',
                                    ]
                                );

                                if (empty(Arr::get($args['headers'], 'content-length.0'))) {
                                    unset($args['headers']['content-length']);
                                }
                            }
                        }
                    }
                }

                $item['args'] = $args;
            }

            if (Arr::exists($item, 'type')) {
                unset($item['type']);
            }

            $trace[] = $item;
        }
        return $trace;
    }
}
