<?php
/** @noinspection PhpInconsistentReturnPointsInspection */

use App\Http\Middleware\ForceJsonResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull;
use Illuminate\Foundation\Http\Middleware\ValidateCsrfToken;
use Illuminate\Http\Middleware\CheckResponseForModifications;
use Illuminate\Http\Middleware\HandleCors;
use Illuminate\Support\Env;
use Illuminate\Validation\ValidationException;
use Symfony\Component\Console\Input\ArgvInput;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

$app = Application::configure(basePath: dirname(__DIR__))
                  ->withRouting(
                      web     : __DIR__ . '/../routes/web.php',
                      api     : __DIR__ . '/../routes/api.php',
                      commands: __DIR__ . '/../routes/console.php',
                      health  : '/up',
                  )
                  ->withMiddleware(function(Middleware $middleware){
                      $middleware->prependToGroup('api', [
                          ForceJsonResponse::class,
                      ]);
                      $middleware->removeFromGroup('api', [ValidateCsrfToken::class]);
                      $middleware->api([
                                           HandleCors::class,
                                           CheckResponseForModifications::class,
                                           ConvertEmptyStringsToNull::class,
                                       ]);
                  })
                  ->withExceptions(function(Exceptions $exceptions){
                      $dontFlash = [
                          'current_password',
                          'password',
                          'password_confirmation',
                      ];
                      $exceptions->dontReportDuplicates();
                      $exceptions->dontFlash($dontFlash);
                      $exceptions->render(function(Throwable $e, \Illuminate\Http\Request $request){
                          if($request->is('api/*')){
                              if($e instanceof AuthenticationException){
                                  return response()->json([
                                                              'status'  => 'error',
                                                              'message' => 'Unauthenticated',
                                                          ],
                                                          401);
                              }

                              if($e instanceof ValidationException){
                                  return response()->json([
                                                              'status'  => 'error',
                                                              'message' => 'Validation failed',
                                                              'errors'  => $e->errors(),
                                                          ],
                                                          422);
                              }

                              if($e instanceof NotFoundHttpException){
                                  return response()->json([
                                                              'status'  => 'error',
                                                              'message' => 'Not Found',
                                                          ],
                                                          404);
                              }
                              $response = [
                                  'status'  => 'error',
                                  'message' => $e->getMessage(),
                                  'code'    => $e->getCode() ?: 500,
                              ];

                              if(app()->isLocal()){
                                  $response['trace'] = $e->getTrace();
                              }

                              return response()->json($response, 500);
                          }
                      });
                  })
                  ->create();

if($app->environmentFile() === '.env.testing'){
    $envPath = '.env.testing';
}
else{
    $envPath = '.env.local';

    $environment = Env::get('APP_ENV');

    if($environment){
        $envPath = '.env.' . $environment;
    }
    if($app->runningInConsole() && ($input = new ArgvInput())->hasParameterOption('--env')){
        $envPath = '.env.' . $input->getParameterOption('--env', 'local');
    }
}
if(file_exists($app->environmentPath() . DIRECTORY_SEPARATOR . $envPath)){
    $app->loadEnvironmentFrom($envPath);
}

return $app;