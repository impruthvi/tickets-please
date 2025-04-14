<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Filters\V1\TicketFilter;
use App\Http\Requests\Api\V1\ReplaceTicketRequest;
use App\Http\Requests\Api\V1\StoreTicketRequest;
use App\Http\Requests\Api\V1\UpdateTicketRequest;
use App\Http\Resources\V1\TicketResource;
use App\Models\Ticket;
use App\Policies\V1\TicketPolicy;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class TicketController extends ApiController
{

    protected $policyClass = TicketPolicy::class;

    /**
     * Display a listing of the resource.
     */
    public function index(TicketFilter $filters)
    {
        return TicketResource::collection(Ticket::filter($filters)->paginate());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTicketRequest $request)
    {
        try {

            if ($this->isAble('store', Ticket::class)) {
                return new TicketResource(Ticket::create($request->mappedAttributes()));
            };

            return $this->error("You are not authorized to create this ticket", 403);
        } catch (ModelNotFoundException $e) {
            return $this->error("User cannot found", 404);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($ticket_id)
    {
        try {
            $ticket = Ticket::findOrFail($ticket_id);

            if ($this->include('author')) {
                return new TicketResource($ticket->load('author'));
            }

            return new TicketResource($ticket);
        } catch (ModelNotFoundException $e) {
            return $this->error("Ticket cannot found", 404);
        }
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTicketRequest $request, $ticket_id)
    {
        try {
            $ticket = Ticket::findOrFail($ticket_id);

            if ($this->isAble('update', $ticket)) {
                return new TicketResource($ticket->update($request->mappedAttributes()));
            }
            return $this->error("You are not authorized to update this ticket", 403);
        } catch (ModelNotFoundException $e) {
            return $this->error("Ticket cannot found", 404);
        }
    }

    /**
     * Replace the specified resource in storage.
     */
    public function replace(ReplaceTicketRequest $request, $ticket_id)
    {
        try {
            $ticket = Ticket::findOrFail($ticket_id);

            if ($this->isAble('replace', $ticket)) {
                return new TicketResource($ticket->replace($request->mappedAttributes()));
            }

            return $this->error("You are not authorized to replace this ticket", 403);
        } catch (ModelNotFoundException $e) {
            return $this->error("Ticket cannot found", 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($ticket_id)
    {
        try {
            $ticket = Ticket::findOrFail($ticket_id);

            if ($this->isAble('delete', $ticket)) {
                $ticket->delete();
                return $this->ok("Ticket deleted successfully");
            }

            return $this->error("You are not authorized to delete this ticket", 403);
        } catch (ModelNotFoundException $e) {
            return $this->error("Ticket cannot found", 404);
        }
    }
}
