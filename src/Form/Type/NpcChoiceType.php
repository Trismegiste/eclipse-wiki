<?php

/*
 * eclipse-wiki
 */

namespace App\Form\Type;

use App\Entity\Transhuman;
use App\Repository\VertexRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Choice for a NPC (return the title)
 */
class NpcChoiceType extends AbstractType
{

    protected VertexRepository $repository;

    public function __construct(VertexRepository $repo)
    {
        $this->repository = $repo;
    }

    public function getParent()
    {
        return ChoiceType::class;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults(['choices' => $this->getNpcList()]);
    }

    protected function getNpcList(): array
    {
        $npcList = [];
        foreach ($this->repository->findByClass(Transhuman::class, ['surnameLang' => ['$ne' => null]]) as $npc) {
            if (!$npc->wildCard) {
                $npcList[$npc->getTitle()] = $npc->getTitle();
            }
        }

        return $npcList;
    }

}
