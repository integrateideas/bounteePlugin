<?php

namespace Integrateideas\Peoplehub\Controller\Api;

use Cake\Controller\Controller;


class ApiController extends Controller
{
	public function initialize()
	{
	    parent::initialize();
	    $this->loadComponent('RequestHandler');
	}

	public function beforeFilter(Event $event)
  	{

	    $origin = $this->request->header('Origin');
	    if($this->request->header('CONTENT_TYPE') != "application/x-www-form-urlencoded; charset=UTF-8"){
	          $this->request->env('CONTENT_TYPE', 'application/json');
	    }
	    $this->request->env('HTTP_ACCEPT', 'application/json');
	    if (!empty($origin)) {
	      $this->response->header('Access-Control-Allow-Origin', $origin);
	    }

	    if ($this->request->method() == 'OPTIONS') {
	      $method  = $this->request->header('Access-Control-Request-Method');
	      $headers = $this->request->header('Access-Control-Request-Headers');
	      $this->response->header('Access-Control-Allow-Headers', $headers);
	      $this->response->header('Access-Control-Allow-Methods', empty($method) ? 'GET, POST, PUT, DELETE' : $method);
	      $this->response->header('Access-Control-Allow-Credentials', 'true');
	      $this->response->header('Access-Control-Max-Age', '120');
	      $this->response->send();
	      die;
	    }
	    // die;
	    $this->response->cors($this->request)
	    ->allowOrigin(['*'])
	    ->allowMethods(['GET', 'POST', 'PUT', 'DELETE', 'OPTIONS'])
	    ->allowHeaders(['X-CSRF-Token','token'])
	    ->allowCredentials()
	    ->exposeHeaders(['Link'])
	    ->maxAge(300)
	    ->build();
    }

}