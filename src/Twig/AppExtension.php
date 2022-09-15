<?php

namespace App\Twig;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;
use App\Entity\Event;

class AppExtension extends AbstractExtension
{
    public function getFilters(): array
    {
        return [
            // If your filter generates SAFE HTML, you should add a third
            // parameter: ['is_safe' => ['html']]
            // Reference: https://twig.symfony.com/doc/2.x/advanced.html#automatic-escaping
            new TwigFilter('filter_name', [$this, 'doSomething']),
        ];
    }

    public function getFunctions(): array
    {
        return [
            new TwigFunction('format_price', [$this, 'formatPrice'], ['is_safe' => ['html']]),
            new TwigFunction('pluralize', [$this, 'pluralize']),
            new TwigFunction('register_link_or_sold_out', [$this, 'registerLinkOrSoldOut'], ['is_safe' => ['html']]),
        ];
    }

    public function formatPrice(Event $event): string
    {
        return $event->isFree() ?'<span class="badge badge-primary">Gratuit</span>' : '$'. $event->getPrice();
    }
}
