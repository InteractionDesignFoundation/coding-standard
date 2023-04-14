<?php declare(strict_types=1);

route('contact-lists.index');
route_relative('contact-lists.index');

$resourceName = 'contact-lists';
route("my-private-profile.$resourceName.show");
