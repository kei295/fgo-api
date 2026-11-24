<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class UserServantController extends Controller
{
    /**
     * ログインユーザーの手持ちサーヴァント一覧を取得する。
     */
    public function index(Request $request): JsonResponse
    {
        $userServants = $request->user()->userServants()->with('servant')->get();

        return response()->json($userServants);
    }

    /**
     * サーヴァントを手持ちに追加する。
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'servant_id' => ['required', 'integer', 'exists:servants,id'],
        ]);

        $user = $request->user();

        if ($user->userServants()->where('servant_id', $validated['servant_id'])->exists()) {
            throw ValidationException::withMessages([
                'servant_id' => 'このサーヴァントは既に手持ちに登録されています。',
            ]);
        }

        $userServant = $user->userServants()->create([
            'servant_id' => $validated['servant_id'],
        ]);

        // level/skillなどはDB側のデフォルト値で入るため、レスポンスに正しい値を含めるためrefreshする
        $userServant->refresh()->load('servant');

        return response()->json($userServant, 201);
    }

    /**
     * 手持ちサーヴァントの育成状況を更新する（送られた項目のみ部分更新）。
     * 霊基再臨（ascension）は編集対象外。
     */
    public function update(Request $request, int $servantId): JsonResponse
    {
        $userServant = $request->user()->userServants()
            ->where('servant_id', $servantId)
            ->firstOrFail();

        $validated = $request->validate(
            [
                'level' => ['sometimes', 'required', 'integer', 'between:1,120'],
                'skill_1' => ['sometimes', 'required', 'integer', 'between:1,10'],
                'skill_2' => ['sometimes', 'required', 'integer', 'between:1,10'],
                'skill_3' => ['sometimes', 'required', 'integer', 'between:1,10'],
                'np_level' => ['sometimes', 'required', 'integer', 'between:1,5'],
                'bond_level' => ['sometimes', 'required', 'integer', 'between:0,16'],
            ],
            [
                'required' => ':attributeを入力してください。',
                'integer' => ':attributeは整数で入力してください。',
                'between' => ':attributeは:min〜:maxの範囲で入力してください。',
            ],
            [
                'level' => 'レベル',
                'skill_1' => 'スキル1',
                'skill_2' => 'スキル2',
                'skill_3' => 'スキル3',
                'np_level' => '宝具レベル',
                'bond_level' => '絆レベル',
            ],
        );

        $userServant->update($validated);

        return response()->json($userServant->load('servant'));
    }

    /**
     * サーヴァントを手持ちから削除する。
     */
    public function destroy(Request $request, int $servantId): JsonResponse
    {
        $userServant = $request->user()->userServants()
            ->where('servant_id', $servantId)
            ->firstOrFail();

        $userServant->delete();

        return response()->json(null, 204);
    }
}
