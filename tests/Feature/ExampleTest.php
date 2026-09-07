<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_application_returns_not_found_without_a_published_homepage(): void
    {
        $this->get('/')->assertNotFound();
    }
}
