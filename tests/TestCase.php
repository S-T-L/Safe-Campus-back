<?php

namespace Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

/**
 * Les tests tournent sur la base de dev, deja migree et seedee : chaque test
 * est enveloppe dans une transaction annulee a la fin, rien n'est jamais
 * supprime. Ils ne doivent donc pas supposer une base vide — creer leurs
 * donnees par factory et ne raisonner que sur elles.
 */
abstract class TestCase extends BaseTestCase
{
    use DatabaseTransactions;
}
