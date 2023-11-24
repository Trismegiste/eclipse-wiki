<?php

/*
 * eclipse-wiki
 */

namespace App\Tests\Controller;

use App\Entity\Background;
use App\Entity\Faction;
use App\Entity\Handout;
use App\Entity\Loveletter;
use App\Entity\Place;
use App\Entity\PlotNode;
use App\Entity\Scene;
use App\Entity\Timeline;
use App\Entity\Transhuman;

/**
 * Images for tests
 */
trait PictureFixture
{

    protected function createTestChart(int $side)
    {
        $target = imagecreatetruecolor($side, $side);
        $bg = imagecolorallocate($target, 0xff, 0xff, 0xff);
        imagefill($target, 0, 0, $bg);

        $fg = imagecolorallocate($target, 0xff, 0, 0);
        imagefilledellipse($target, $side / 2, $side / 2, $side / 2, $side / 2, $fg);

        return $target;
    }

    protected function createRandomScene()
    {
        $obj = new Scene('scene' . rand());
        $obj->setContent('information');
        return $obj;
    }

    protected function createRandomTranshuman()
    {
        $obj = new Transhuman('takeshi' . rand(), new Background('bg'), new Faction('diplo'));
        $obj->setContent('information');
        return $obj;
    }

    protected function createRandomPlace()
    {
        $obj = new Place('place' . rand());
        $obj->setContent('information');
        return $obj;
    }

    protected function createRandomLoveletter()
    {
        $obj = new Loveletter('loveletter' . rand());
        $obj->context = 'information';
        $obj->drama = 'drama';
        return $obj;
    }

    protected function createRandomHandout()
    {
        $obj = new Handout('handout' . rand());
        $obj->pcInfo = 'information';
        $obj->gmInfo = 'secret';
        return $obj;
    }

    protected function createRandomTimeline()
    {
        $obj = new Timeline('time' . rand());
        $obj->elevatorPitch = 'information';
        $obj->setTree(new PlotNode('root'));
        return $obj;
    }

}
