<?php

namespace App\Http\Controllers;

use App\Http\Requests\Test\DeclareResultRequest;
use App\Http\Requests\Test\DepositRequest;
use App\Http\Requests\Test\StoreEntryRequest;
use App\Http\Requests\Test\StoreGameRequest;
use App\Repositories\WalletRepository;
use App\Services\EntryService;
use App\Services\GameService;
use App\Services\ResultService;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;

class TestPlatformController extends Controller
{
    public function createGame(StoreGameRequest $request, GameService $gameService): JsonResponse
    {
        $game = $gameService->createGame($request->validated());

        return response()->json([
            'message' => 'Game created successfully.',
            'data' => $game,
        ], 201);
    }

    public function deposit(
        DepositRequest $request,
        WalletService $walletService,
        WalletRepository $walletRepository,
    ): JsonResponse {
        $payload = $request->validated();
        $transaction = $walletService->deposit(
            (int) $payload['user_id'],
            $payload['amount'],
            $payload['reference'] ?? null,
        );

        return response()->json([
            'message' => 'Deposit completed successfully.',
            'data' => [
                'transaction' => $transaction,
                'wallet' => $walletRepository->getUserWallet((int) $payload['user_id']),
            ],
        ], 201);
    }

    public function placeEntry(
        StoreEntryRequest $request,
        EntryService $entryService,
        WalletRepository $walletRepository,
    ): JsonResponse {
        $payload = $request->validated();
        $entry = $entryService->placeEntry((int) $payload['user_id'], $payload);

        return response()->json([
            'message' => 'Entry placed successfully.',
            'data' => [
                'entry' => $entry->load(['user', 'game']),
                'wallet' => $walletRepository->getUserWallet((int) $payload['user_id']),
            ],
        ], 201);
    }

    public function listGames(GameService $gameService): JsonResponse
    {
        return response()->json([
            'data' => [
                'active_games' => $gameService->getActiveGames(),
                'open_games' => $gameService->getOpenGames(),
            ],
        ]);
    }

    public function declareResult(DeclareResultRequest $request, ResultService $resultService): JsonResponse
    {
        $payload = $request->validated();
        $result = $resultService->declareResult(
            (int) $payload['game_id'],
            $payload['winning_number'] ?? null,
        );

        return response()->json([
            'message' => 'Result declared successfully.',
            'data' => $result,
        ]);
    }
}
