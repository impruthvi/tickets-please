<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Filters\V1\TicketFilter;
use App\Http\Requests\Api\V1\ReplaceTicketRequest;
use App\Http\Requests\Api\V1\StoreTicketRequest;
use App\Http\Requests\Api\V1\UpdateTicketRequest;
use App\Http\Resources\V1\TicketResource;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class AuthorTicketsController extends ApiController
{
    public function index($author_id, TicketFilter $filters)
    {
        return TicketResource::collection(
            Ticket::where('user_id', $author_id)->filter($filters)->paginate()
        );
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store($author_id, StoreTicketRequest $request)
    {
        try {
            User::findOrFail($author_id);
        } catch (\Exception $e) {
            return $this->error("User cannot found", 404);
        }

        return new TicketResource(Ticket::create($request->mappedAttributes()));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($author_id, $ticket_id)
    {
        try {
            $ticket = Ticket::findOrFail($ticket_id);

            if ($ticket->user_id == $author_id) {
                $ticket->delete();
                return $this->ok("Ticket deleted successfully");
            }

            return $this->error("Ticket cannot found", 404);
        } catch (ModelNotFoundException $e) {
            return $this->error("Ticket cannot found", 404);
        }
    }

    /**
     * Replace the specified resource in storage.
     */
    public function replace(ReplaceTicketRequest $request, $author_id, $ticket_id)
    {
        try {
            $ticket = Ticket::findOrFail($ticket_id);

            if ($ticket->user_id == $author_id) {

                $ticket->update($request->mappedAttributes());

                return new TicketResource($ticket);
            }
            return $this->error("Ticket cannot found", 404);
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
            $ticket = Ticket::findOrFail($ticket_id);

            if ($ticket->user_id == $author_id) {

                $ticket->update($request->mappedAttributes());

                return new TicketResource($ticket);
            }
            return $this->error("Ticket cannot found", 404);
        } catch (ModelNotFoundException $e) {
            return $this->error("Ticket cannot found", 404);
        }
    }
}
