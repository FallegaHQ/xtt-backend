<?php

namespace Tests\Unit\Http\Middleware;

use App\Http\Middleware\ForceJsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\HeaderBag;
use function octdec;

class ForceJsonResponseTest extends TestCase{
    private ForceJsonResponse $middleware;

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    #[Test]
    public function shouldForceAcceptJsonHeader(): void{
        $request          = $this->createMock(Request::class);
        $request->headers = new HeaderBag();

        $closure = function($req){
            $response = new Response();
            $this->assertTrue($req->headers->has('Accept'));
            $this->assertEquals('application/json', $req->headers->get('Accept'));

            return $response;
        };

        $this->middleware->handle($request, $closure);
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    #[Test]
    public function shouldSetContentTypeIfMissing(): void{
        $request          = $this->createMock(Request::class);
        $request->headers = new HeaderBag();

        $response = new Response();

        $closure = function() use ($response){
            return $response;
        };

        $result = $this->middleware->handle($request, $closure);

        $this->assertTrue($result->headers->has('Content-Type'));
        $this->assertEquals('application/json', $result->headers->get('Content-Type'));
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    #[Test]
    public function shouldNotOverrideExistingContentType(): void{
        $request          = $this->createMock(Request::class);
        $request->headers = new HeaderBag();

        $response = new Response();
        $response->headers->set('Content-Type', 'text/plain');

        $closure = function() use ($response){
            return $response;
        };

        $result = $this->middleware->handle($request, $closure);

        $this->assertEquals('text/plain', $result->headers->get('Content-Type'));
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    #[Test]
    public function shouldConvertJsonToJsonpWhenRequested(): void{
        $request          = $this->createMock(Request::class);
        $request->headers = new HeaderBag();

        $request->expects($this->any())->method('has')
                ->willReturnMap([
                                    [
                                        'format',
                                        true,
                                    ],
                                    [
                                        'callback',
                                        true,
                                    ],
                                ]);

        $request->expects($this->atLeast(1))->method('input')
                ->willReturnMap([
                                    [
                                        'format',
                                        null,
                                        'jsonp',
                                    ],
                                    [
                                        'callback',
                                        null,
                                        'myCallback',
                                    ],
                                ]);

        $response = new Response('{"data":"test"}');
        $response->headers->set('Content-Type', 'application/json');

        $closure = function() use ($response){
            return $response;
        };

        $result = $this->middleware->handle($request, $closure);

        $this->assertEquals('application/javascript', $result->headers->get('Content-Type'));
        $this->assertEquals('myCallback({"data":"test"})', $result->getContent());
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    #[Test]
    public function shouldNotConvertToJsonpIfFormatIsNotJsonp(): void{
        $request          = $this->createMock(Request::class);
        $request->headers = new HeaderBag();

        $request->expects($this->any())->method('has')
                ->willReturnMap([
                                    [
                                        'format',
                                        true,
                                    ],
                                    [
                                        'callback',
                                        true,
                                    ],
                                ]);

        $request->expects($this->any())->method('input')
                ->willReturnMap([
                                    [
                                        'format',
                                        null,
                                        'xml',
                                    ],
                                    [
                                        'callback',
                                        null,
                                        'myCallback',
                                    ],
                                ]);

        $response = new Response('{"data":"test"}');
        $response->headers->set('Content-Type', 'application/json');

        $closure = function() use ($response){
            return $response;
        };

        $result = $this->middleware->handle($request, $closure);

        $this->assertEquals('application/json', $result->headers->get('Content-Type'));
        $this->assertEquals('{"data":"test"}', $result->getContent());
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    #[Test]
    public function shouldAddCacheControlHeadersForJsonResponses(): void{
        $request          = $this->createMock(Request::class);
        $request->headers = new HeaderBag();

        $response = new Response();
        $response->headers->set('Content-Type', 'application/json');

        $closure = function() use ($response){
            return $response;
        };

        $result = $this->middleware->handle($request, $closure);

        $this->assertEquals('no-cache, private', $result->headers->get('Cache-Control'));
        $this->assertEquals('*', $result->headers->get('Access-Control-Allow-Origin'));
    }

    protected function setUp(): void{
        parent::setUp();
        $this->middleware = new ForceJsonResponse();
    }
}