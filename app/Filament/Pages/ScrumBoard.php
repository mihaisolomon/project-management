<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Models\Ticket;
use App\Models\TicketStatus;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class ScrumBoard extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-view-boards';

    protected static string $view = 'filament.pages.scrum-board';

    protected static ?string $slug = 'scrum-board';

    protected static ?int $navigationSort = 4;

    public function getStatuses(): Collection
    {
        $query = TicketStatus::query();

            $query->whereNull('project_id');

        return $query->orderBy('order')
            ->get()
            ->map(function ($item) {
                $query = Ticket::query();

                $query->where('status_id', $item->id);

                return [
                    'id' => $item->id,
                    'title' => $item->name,
                    'color' => $item->color,
                    'size' => $query->count(),
                    'add_ticket' => $item->is_default && auth()->user()->can('Create ticket')
                ];
            });
    }

    public function getRecords(): Collection
    {
        $query = Ticket::query();

        $query->with(['project', 'owner', 'responsible', 'status', 'type', 'priority', 'epic']);
//        $query->where('project_id', $this->project->id);
//        if (sizeof($this->users)) {
//            $query->where(function ($query) {
//                return $query->whereIn('owner_id', $this->users)
//                    ->orWhereIn('responsible_id', $this->users);
//            });
//        }
//        if (sizeof($this->types)) {
//            $query->whereIn('type_id', $this->types);
//        }
//        if (sizeof($this->priorities)) {
//            $query->whereIn('priority_id', $this->priorities);
//        }
//        if ($this->includeNotAffectedTickets) {
//            $query->whereNull('responsible_id');
//        }
        $query->where(function ($query) {
            return $query->where('owner_id', auth()->user()->id)
                ->orWhere('responsible_id', auth()->user()->id)
                ->orWhereHas('project', function ($query) {
                    return $query->where('owner_id', auth()->user()->id)
                        ->orWhereHas('users', function ($query) {
                            return $query->where('users.id', auth()->user()->id);
                        });
                });
        });
        return $query->get()
            ->map(fn(Ticket $item) => [
                'id' => $item->id,
                'code' => $item->code,
                'title' => $item->name,
                'owner' => $item->owner,
                'type' => $item->type,
                'responsible' => $item->responsible,
                'project' => $item->project,
                'status' => $item->status->id,
                'priority' => $item->priority,
                'epic' => $item->epic,
                'relations' => $item->relations,
                'totalLoggedHours' => $item->totalLoggedSeconds ? $item->totalLoggedHours : null
            ]);
    }

    public function isMultiProject(): bool
    {
        return false;
    }
}
