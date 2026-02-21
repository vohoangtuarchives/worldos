<?php

namespace Tuzy\Domain\Governance\Events;

use App\Models\StyleProposal;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class StyleProposalCreated
{
    use Dispatchable, SerializesModels;

    public function __construct(public StyleProposal $proposal) {}
}
