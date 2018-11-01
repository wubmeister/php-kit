<?php

namespace RestKit\Resource;

use Psr\Http\Message\ServerRequestInterface;
use Zend\Diactoros\Response\JsonResponse;
use Zend\Diactoros\Response\HtmlResponse;
use CoreKit\Resolver;
use TemplateKit\Template;
use RestKit\Exception\NotAllowedException;
use RestKit\Exception\BadRequestException;

use AuthKit\Auth;
use AuthKit\Acl\Acl;
use RestKit\Router;

abstract class AbstractResource
{
    protected $request;
    protected $templateResolver;
    protected $layoutTemplate;
    protected $responseFormat;
    protected $name = 'resource';
    protected $template;
    protected $auth;
    protected $router;

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

        $this->template = $action;

        $identity = null;
        if ($this->auth) {
            $identity = $this->auth->getIdentity();
            $role = $identity && isset($identity->role) ? $identity->role : 'Guest';
            $isAllowed = Acl::isAllowed($role, $this->name, $action);
            if (!$isAllowed) {
                throw new NotAllowedException('The action \'' . $action . '\' is not allowed for this user');
            }
        }

        if ($id !== null) {
            $result = $this->$action($id);
        } else {
            $result = $this->$action();
        }

        if ($this->responseFormat == 'html') {
            $html = "{$this->name}/{$this->template}";
            if ($this->templateResolver) {
                $file = $this->templateResolver->resolve("{$this->name}/{$this->template}.phtml");
                if ($file) {
                    $template = new Template($file);
                    if (is_array($result)) {
                        foreach ($result as $key => $value) {
                            $template->assign($key, $value);
                        }
                    } else {
                        $template->assign('result', $result);
                    }
                    if ($this->layoutTemplate) {
                        $this->trigger('prepareLayout', $this->layoutTemplate);
                        $this->layoutTemplate->assign('identity', $identity);
                        $this->layoutTemplate->assign('content', $template);
                        $html = $this->layoutTemplate->render();
                    } else {
                        $template->assign('identity', $identity);
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

    public function setLayoutTemplate(Template $template)
    {
        $this->layoutTemplate = $template;
    }

    public function setResponseFormat(string $format)
    {
        $this->responseFormat = $format;
    }

    public function setAuth(Auth $auth)
    {
        $this->auth = $auth;
    }

    public function setRouter(Router $router)
    {
        $this->router = $router;
    }

    abstract public function index();
    abstract public function detail($id);
    abstract public function add();
    abstract public function update($id);
    abstract public function delete($id);
}
