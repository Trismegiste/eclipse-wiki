<?php

/*
 * Eclipse Wiki
 */

namespace App\Controller;

use App\Service\MediaWiki;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Description of TemplateRender
 */
class TemplateRender extends AbstractController
{

    protected $repository;

    public function __construct(MediaWiki $source)
    {
        $this->repository = $source;
    }

    /**
     * @Route("/template/{name}")
     */
    public function generate(string $name, Request $request): Response
    {
        $params = $this->repository->getTemplateData($name);

        $builder = $this->createFormBuilder();
        foreach ($params as $field => $info) {
            $builder->add($field);
        }
        $form = $builder
            ->add('submit', SubmitType::class)
            ->getForm();

        return $this->render('front/template_form.html.twig', ['form' => $form->createView()]);
    }

}
