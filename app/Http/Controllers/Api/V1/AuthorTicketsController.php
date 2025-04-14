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

class AuthorTicketsController extends ApiController
{

    protected $policyClass = TicketPolicy::class;

    public function index($author_id, TicketFilter $filters)
    {
        return TicketResource::collection(
            Ticket::where('user_id', $author_id)->filter($filters)->paginate()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreTicketRequest $request, $author_id)
    {

        if ($this->isAble('store', Ticket::class)) {
            return new TicketResource(Ticket::create($request->mappedAttributes([
                'author' => 'user_id'
            ])));
        }

        return $this->error("You are not authorized to create this ticket", 403);
    }

    /**
     * Replace the specified resource in storage.
     */
    public function replace(ReplaceTicketRequest $request, $author_id, $ticket_id)
    {
        try {
            $ticket = Ticket::whereId($ticket_id)->whereUserId($author_id)->firstOrFail();

            if ($this->isAble('replace', $ticket)) {

                $ticket->update($request->mappedAttributes());

                return new TicketResource($ticket);
            }

            return $this->error("You are not authorized to replace this ticket", 403);
        } catch (ModelNotFoundException $e) {
            return $this->error("Ticket cannot found", 404);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateTicketRequest $request, $author_id, $ticket_id)
    {
        try {
            $ticket = Ticket::whereId($ticket_id)->whereUserId($author_id)->firstOrFail();

            if ($this->isAble('update', $ticket)) {
                $ticket->update($request->mappedAttributes());
                return new TicketResource($ticket);
            }

            return $this->error("You are not authorized to update this ticket", 403);
        } catch (ModelNotFoundException $e) {
            return $this->error("Ticket cannot found", 404);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($author_id, $ticket_id)
    {
        try {
            $ticket = Ticket::whereId($ticket_id)->whereUserId($author_id)->firstOrFail();

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
