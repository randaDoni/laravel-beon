<?php

use App\Http\Controllers\AccidentialDataMonthlyPaymentController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ContributionAccidentialController;
use App\Http\Controllers\HouseController;
use App\Http\Controllers\HouseResidentController;
use App\Http\Controllers\MasterDataMonthlyPaymentController;
use App\Http\Controllers\ResidentController;
use App\Models\Contribution_monthly;
use App\Models\MasterDataMonthlyPayment;
use Tymon\JWTAuth\Facades\JWTAuth;

// Public route
Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:api'])->group(function(){
    Route::group(['prefix' => 'residents', 'as' => 'residents.', 'namespace'=> 'residents.'], function () {
        Route::get('/ResidentName', [ResidentController::class, 'ResidentName']); // HARUS DI ATAS
        Route::get('/', [ResidentController::class, 'index']);
        Route::post('/', [ResidentController::class, 'store']);
        Route::get('/{id}', [ResidentController::class, 'show']);
        Route::put('/{id}', [ResidentController::class, 'update']);
        Route::delete('/{id}', [ResidentController::class, 'destroy']);
    });


    Route::group(['prefix' => 'houses', 'as' => 'houses.','namespace'=> 'houses.'], function () {
        Route::get('/AddressName', [HouseController::class, 'AddressName']);
        Route::get('/', [HouseController::class, 'index']);
        Route::post('/', [HouseController::class, 'store']);
        Route::get('/{id}', [HouseController::class, 'show']);
        Route::put('/{id}', [HouseController::class, 'update']);
        Route::delete('/{id}', [HouseController::class, 'destroy']);
    });

    Route::group(['prefix' => 'house_residents', 'as' => 'house_residents.','namespace'=> 'house_residents.'], function () {
        Route::get('/', [HouseResidentController::class, 'index']);
        Route::post('/', [HouseResidentController::class, 'store']);
        Route::get('/{id}', [HouseResidentController::class, 'show']);
        Route::put('/{id}', [HouseResidentController::class, 'update']);
        Route::delete('/{id}', [HouseResidentController::class, 'destroy']);
    });

    Route::group(['prefix' => 'master_data_contribution_monthly', 'as' => 'master_data_contribution_monthly.','namespace'=> 'master_data_contribution_monthly.'], function () {
        Route::get('/', [MasterDataMonthlyPaymentController::class, 'index']);
        Route::post('/', [MasterDataMonthlyPaymentController::class, 'store']);
        Route::get('/{id}', [MasterDataMonthlyPaymentController::class, 'show']);
        Route::put('/{id}', [MasterDataMonthlyPaymentController::class, 'update']);
        Route::delete('/{id}', [MasterDataMonthlyPaymentController::class, 'destroy']);
    });

    Route::group(['prefix' => 'contribution_monthly', 'as' => 'contribution_monthly.','namespace'=> 'contribution_monthly.'], function () {
        Route::get('/', [Contribution_monthly::class, 'index']);
        Route::post('/', [Contribution_monthly::class, 'store']);
        Route::get('/{id}', [Contribution_monthly::class, 'show']);
        Route::put('/{id}', [Contribution_monthly::class, 'update']);
        Route::delete('/{id}', [Contribution_monthly::class, 'destroy']);
    });

    Route::group(['prefix' => 'accidential_data_monthly_payment', 'as' => 'accidential_data_monthly_payment.','namespace'=> 'accidential_data_monthly_payment.'], function () {
        Route::get('/', [AccidentialDataMonthlyPaymentController::class, 'index']);
        Route::post('/', [AccidentialDataMonthlyPaymentController::class, 'store']);
        Route::get('/{id}', [AccidentialDataMonthlyPaymentController::class, 'show']);
        Route::put('/{id}', [AccidentialDataMonthlyPaymentController::class, 'update']);
        Route::delete('/{id}', [AccidentialDataMonthlyPaymentController::class, 'destroy']);
    });

    Route::group(['prefix' => 'contribution_accidential', 'as' => 'contribution_accidential.','namespace'=> 'contribution_accidential.'], function () {
        Route::get('/', [ContributionAccidentialController::class, 'index']);
        Route::post('/', [ContributionAccidentialController::class, 'store']);
        Route::get('/{id}', [ContributionAccidentialController::class, 'show']);
        Route::put('/{id}', [ContributionAccidentialController::class, 'update']);
        Route::delete('/{id}', [ContributionAccidentialController::class, 'destroy']);
    });
});
