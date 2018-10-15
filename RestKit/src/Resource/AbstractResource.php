<?php

namespace RestKit\Resource;

use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\Response\HtmlResponse;
use CoreKit\Resolver;
use TemplateKit\Template;

abstract class AbstractResource
{
    protected $request;
    protected $templateResolver;
    protected $layoutTemplate;
    protected $responseFormat;
    protected $name = 'resource';

    public function __invoke(ServerRequestInterface $request)
    {
        $this->request = $request;

        $action = 'index';
        $method = $request->getMethod();
        $id = $request->getAttribute('id');
        $query = $request->getQueryParams();

        if (isset($query['mode'])) {
            $action = $query['mode'];
        } else if ($method == 'POST' || $method == 'PUT') {
            $action = $id ? 'update' : 'add';
        } else if ($method == 'DELETE') {
            if (!$id) {
                throw new BadRequestException('DELETE request should provide an ID');
            }
            $action = 'delete';
        } else if ($method == 'GET') {
            $action = $id ? 'detail' : 'index';
        } else {
            throw new BadRequestException('Method ' . $method . ' not supported');
        }

        // $user = AuthKit\Identity::getCurrent();
        // $role = $user ? $user->role : 'Guest';
        // $isAllowed = AuthKit\Acl::isAllowed($this->name, $role, $action);
        // if (!$isAllowed) {
        //     throw new NotAllowedException('The action \'' . $action . '\' is not allowed for this user');
        // }

        if ($id) {
            $result = $this->$action($id);
        }
        $result = $this->$action();

        if ($this->responseFormat == 'html') {
            $html = "{$this->name}/{$action}";
            if ($this->templateResolver) {
                $file = $this->templateResolver->resolve("{$this->name}/{$action}.phtml");
                if ($file) {
                    $template = new Template($file);
                    foreach ($result as $key => $value) {
                        $template->assign($key, $value);
                    }
                    if ($this->layoutTemplate && ($file = $this->templateResolver->resolve($this->layoutTemplate))) {
                        $layout = new Template($file);
                        $layout->assign('content', $template);
                        $html = $layout->render();
                    } else {
                        $html = $template->render();
                    }
                }
            }
            return new HtmlResponse($html);
        }

        $responseData = [
            'success' => true,
            'data' => $result
        ];

        return new JsonResponse($responseData);
    }

    public function trigger($event, ...$arguments)
    {
        if (method_exists($this, $event)) {
            call_user_func_array([ $this, $event ], $arguments);
        }
    }

    public function setTemplateResolver(Resolver $resolver)
    {
        $this->templateResolver = $resolver;
    }

    public function setLayoutTemplate(string $template)
    {
        $this->layoutTemplate = $template;
    }

    public function setResponseFormat(string $format)
    {
        $this->responseFormat = $format;
    }

    abstract public function index();
    abstract public function detail($id);
    abstract public function add();
    abstract public function update($id);
    abstract public function delete($id);
}
