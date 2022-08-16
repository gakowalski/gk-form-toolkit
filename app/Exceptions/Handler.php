<?php

namespace Kowalski\Laravel\App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'password',
        'password_confirmation',
    ];

    /**
     * Get the default context variables for logging.
     *
     * @return array
     */
    protected function context()
    {
      if (app()->runningInConsole()){
        return [
          'user_agent' => 'CONSOLE',
        ];
      } else {
        return array_merge(parent::context(), [
          'user_email' => \Auth::user()?->email,
          'user_agent' => request()->header('user-agent') ?? 'not request?',
          'route' => request()->route() ?? $_SERVER['REQUEST_URI'] ?? 'not request?',
          'data' => request()->all(),
        ]);
      }
    }

    /**
     * Report or log an exception.
     *
     * @param  \Throwable  $exception
     * @return void
     *
     * @throws \Exception
     */
    public function report(Throwable $exception)
    {
      $trace = $exception->getTrace();

      if (is_array($trace) && isset($trace[0])) {
        \Log::error(sprintf(
            "\n\tUncaught exception '%s'\n\twith message '%s'\n\tin  %s:%d\n\twith context: ",
            get_class($exception),
            $exception->getMessage(),
            $exception->getTrace()[0]['file'] ?? 'unknown file',
            $exception->getTrace()[0]['line'] ?? 'unknown line'
        ), $this->context());
      } else {
        \Log::error(sprintf(
            "\n\tUncaught exception '%s'\n\twith message '%s'\n\twith context: ",
            get_class($exception),
            $exception->getMessage()
        ), $this->context());
      }
      // parent::report($exception);
    }

    /**
     * Render an exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Throwable  $exception
     * @return \Symfony\Component\HttpFoundation\Response
     *
     * @throws \Throwable
     */
    public function render($request, Throwable $exception)
    {
        return parent::render($request, $exception);
    }
}
