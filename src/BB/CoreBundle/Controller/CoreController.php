<?php
namespace BB\CoreBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
class CoreController extends Controller
{
  public function indexAction()
  {
    return $this->render('BBCoreBundle:Core:index.html.twig');
  }
}