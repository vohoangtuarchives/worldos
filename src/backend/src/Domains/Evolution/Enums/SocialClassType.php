<?php

namespace WorldOS\Domains\Evolution\Enums;

enum SocialClassType: string
{
    case NOBILITY = 'nobility';     // Ruling class, correlates with Authority/Stability
    case PRIESTHOOD = 'priesthood'; // Spiritual leaders, correlates with Ritual/Faith
    case MERCHANT = 'merchant';     // Traders, correlates with Trade/Knowledge (sometimes)
    case WARRIOR = 'warrior';       // Military, correlates with Militarism/Aggression
    case PEASANTRY = 'peasantry';   // Working mass, correlates with Resilience/Population
    case INTELLECTUAL = 'intellectual'; // Scholars, correlates with Knowledge/Reform
}


