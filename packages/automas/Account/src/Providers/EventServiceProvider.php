<?php

namespace Automas\Account\Providers;

use App\Events\ApprovePurchaseReturn;
use App\Events\ApproveSalesReturn;
use App\Events\CreateTransfer;
use App\Events\DefaultData;
use App\Events\DestroyTransfer;
use App\Events\GivePermissionToRole;
use App\Events\PostPurchaseInvoice;
use App\Events\PostSalesInvoice;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Automas\Account\Listeners\ApproveHolidayzRoomBookingListener;
use Automas\Account\Listeners\ApproveMedicalOrderPaymentListener;
use Automas\Account\Listeners\ApprovePettyCashListener;
use Automas\Account\Listeners\BankAccountFieldUpdate;
use Automas\Account\Listeners\CreateDebitNoteFromReturn;
use Automas\Account\Listeners\CreateCreditNoteFromReturn;
use Automas\Account\Listeners\UpdateMobileServicePaymentStatusLis;
use Automas\Account\Listeners\DataDefault;
use Automas\Account\Listeners\PostPurchaseInvoiceListener;
use Automas\Account\Listeners\CreateTransferListener;
use Automas\Account\Listeners\DestroyTransferListener;
use Automas\Account\Listeners\GiveRoleToPermission;
use Automas\Account\Listeners\PostSalesInvoiceListener;
use Automas\Account\Listeners\PostProjectPaymentListener;
use Automas\Account\Listeners\UpdateRetainerPaymentStatusListener;
use Automas\Retainer\Events\UpdateRetainerPaymentStatus;
use Automas\Account\Listeners\UpdateCommissionPaymentStatusListener;
use Automas\Commission\Events\UpdateCommissionPaymentStatus;
use Automas\Account\Listeners\PaySalaryListener;
use Automas\Hrm\Events\PaySalary;
use Automas\Account\Listeners\CreatePosListener;
use Automas\Account\Listeners\ApprovePosReturnListener;
use Automas\Fleet\Events\MarkFleetBookingPaymentPaid;
use Automas\MobileServiceManagement\Events\UpdateMobileServicePaymentStatus;
use Automas\Pos\Events\CreatePos;
use Automas\Pos\Events\ApprovePosReturn;
use Automas\Account\Listeners\MarkFleetBookingPaymentPaidListener;
use Automas\Fleet\Events\CraeteFleetBookingPayment;
use Automas\MobileServiceManagement\Events\CreateMobileServicePayment;
use Automas\Account\Listeners\BeautyBookingPaymentListener;
use Automas\DairyCattleManagement\Events\CreateDairyCattlePayment;
use Automas\DairyCattleManagement\Events\UpdateDairyCattlePaymentStatus;
use Automas\Account\Listeners\UpdateDairyCattlePaymentStatusListener;
use Automas\CateringManagement\Events\CreateCateringOrderPayment;
use Automas\CateringManagement\Events\UpdateCateringOrderPaymentStatus;
use Automas\Account\Listeners\UpdateCateringOrderPaymentStatusListener;
use Automas\Account\Listeners\UpdateSalesAgentCommissionPaymentStatusLis;
use Automas\Account\Listeners\ApproveSalesAgentCommissionAdjustmentLis;
use Automas\Account\Listeners\CompleteWarrantyClaimListener;
use Automas\Account\Listeners\ConvertSalesRetainerListener;
use Automas\Account\Listeners\MarkBeautyBookingPaymentPaidListener;
use Automas\Account\Listeners\UpdateDairyCattleExpenseTrackingStatusListener;
use Automas\BeautySpaManagement\Events\BeautyBookingPayments;
use Automas\BeautySpaManagement\Events\CreateBeautyBookingPayment;
use Automas\BeautySpaManagement\Events\MarkBeautyBookingPaymentPaid;
use Automas\CateringManagement\Events\CreateCateringExpenseTracking;
use Automas\CateringManagement\Events\MarkCateringExpenseTrackingAsPaid;
use Automas\CateringManagement\Events\UpdateCateringExpenseTracking;
use Automas\Commission\Events\CreateCommissionPayment;
use Automas\CourierManagement\Events\CreateCourierPayment;
use Automas\CourierManagement\Events\UpdateCourierPayment;
use Automas\DairyCattleManagement\Events\CreateDairyCattleExpenseTracking;
use Automas\DairyCattleManagement\Events\UpdateDairyCattleExpenseTracking;
use Automas\DairyCattleManagement\Events\UpdateDairyCattleExpenseTrackingStatus;
use Automas\EventsManagement\Events\CreateEventBookingPayment;
use Automas\Fleet\Events\CreateFleetExpense;
use Automas\Fleet\Events\UpdateFleetExpense;
use Automas\GymManagement\Events\CreateMembershipPlanPayment;
use Automas\PropertyManagement\Events\CreatePropertyPayment;
use Automas\Hrm\Events\CreatePayroll;
use Automas\Hrm\Events\UpdatePayroll;
use Automas\LaundryManagement\Events\CreateLaundryExpense;
use Automas\LaundryManagement\Events\CreateLaundryPayment;
use Automas\LaundryManagement\Events\UpdateLaundryExpense;
use Automas\LegalCaseManagement\Events\CreateCaseExpense;
use Automas\LegalCaseManagement\Events\CreateFeeReceive;
use Automas\LegalCaseManagement\Events\UpdateCaseExpense;
use Automas\LegalCaseManagement\Events\UpdateFeeReceive;
use Automas\MedicalLabManagement\Events\CreateMedicalOrderPayment;
use Automas\ParkingManagement\Events\CreateParkingPayment;
use Automas\RentalManagement\Events\CreateRentalMaintenance;
use Automas\RentalManagement\Events\UpdateRentalMaintenance;
use Automas\Retainer\Events\ConvertSalesRetainer;
use Automas\SalesAgent\Events\CreateSalesAgentCommissionPayment;
use Automas\SalesAgent\Events\UpdateSalesAgentCommissionPaymentStatus;
use Automas\SalesAgent\Events\ApproveSalesAgentCommissionAdjustment;
use Automas\SocietyManagement\Events\CreateSocietyMaintenanceBillPayment;
use Automas\VehicleBookingManagement\Events\CreateVehicleBookingPayment;
use Automas\Warranty\Events\CreateWarrantyClaim;
use Automas\Account\Listeners\MarkCateringExpenseTrackingAsPaidListener;
use Automas\Holidayz\Events\CreateHolidayzRoomBooking;
use Automas\Holidayz\Events\HolidayzBookingPayments;
use Automas\Holidayz\Events\UpdateHolidayzRoomBooking;
use Automas\Account\Listeners\HolidayzBookingPaymentsListener;
use Automas\Fleet\Events\PostFleetExpense;
use Automas\Holidayz\Events\ApproveHolidayzRoomBooking;
use Automas\Account\Listeners\PostFleetExpenseListener;
use Automas\EventsManagement\Events\UpdateEventBookingPaymentStatus;
use Automas\Account\Listeners\UpdateEventBookingPaymentStatusListener;
use Automas\EventsManagement\Events\EventBookingPayments;
use Automas\Account\Listeners\EventBookingPaymentsListener;
use Automas\Account\Listeners\LaundryBookingPaymentsListener;
use Automas\Account\Listeners\MarkCaseExpenseAsPaidListner;
use Automas\Account\Listeners\MarkFeeReceiveAsClearedListener;
use Automas\Account\Listeners\UpdateVehicleBookingPaymentStatusListener;
use Automas\Account\Listeners\VehicleBookingPaymentsListener;
use Automas\GymManagement\Events\MembershipPlanAssigned;
use Automas\LegalCaseManagement\Events\MarkCaseExpenseAsPaid;
use Automas\LegalCaseManagement\Events\MarkFeeReceiveAsCleared;
use Automas\VehicleBookingManagement\Events\UpdateVehicleBookingPaymentStatus;
use Automas\VehicleBookingManagement\Events\VehicleBookingPayments;
use Automas\Account\Listeners\MembershipPlanAssignedListener;
use Automas\Account\Listeners\ParkingBookingPaymentsListener;
use Automas\Account\Listeners\UpdateLaundryExpenseStatusListener;
use Automas\Account\Listeners\UpdateLaundryPaymentStatusListener;
use Automas\Account\Listeners\UpdateParkingPaymentStatusListener;
use Automas\LaundryManagement\Events\LaundryBookingPayments;
use Automas\LaundryManagement\Events\UpdateLaundryExpenseStatus;
use Automas\LaundryManagement\Events\UpdateLaundryPaymentStatus;
use Automas\MedicalLabManagement\Events\ApproveMedicalOrderPayment;
use Automas\ParkingManagement\Events\ParkingBookingPayments;
use Automas\ParkingManagement\Events\UpdateParkingPaymentStatus;
use Automas\PettyCashManagement\Events\ApprovePettyCash;
use Automas\PettyCashManagement\Events\CreatePettyCash;
use Automas\PettyCashManagement\Events\UpdatePettyCash;
use Automas\Warranty\Events\CompleteWarrantyClaim;
use Automas\Pos\Events\CreatePosBillingCounter;
use Automas\Pos\Events\UpdatePosBillingCounter;
use Automas\VehicleTrade\Events\CreateVehicletradePayment;
use Automas\VehicleTrade\Events\CreateServiceHistory;
use Automas\VehicleTrade\Events\UpdateServiceHistory;
use Automas\OpticalAndEyeCareCenter\Events\CreateEyewearOrder;
use Automas\OpticalAndEyeCareCenter\Events\UpdateEyewearOrder; 
use Automas\MovieShowBookingSystem\Events\CreateMovieFoodOrder;
use Automas\MovieShowBookingSystem\Events\UpdateMovieFoodOrder;
use Automas\SecurityGuardManagement\Events\CreateSecurityPayment;
use Automas\TailoringFashiondesign\Events\CreateTailorPayment;
use Automas\LockerAndSafeDeposit\Events\CreateLockerPayment;
use Automas\Consultancy\Events\CreateConsultancyPayment;
use Automas\MovieShowBookingSystem\Events\CreateMovieBookingPayment;
use Automas\DietAndNutritionConsultant\Events\CreateDietMemberPayment;
use Automas\DietAndNutritionConsultant\Events\CreateDietAppointment;
use Automas\DJAndOrchestraManagement\Events\CreateDJOrchestraEventPayment;
use Automas\DJAndOrchestraManagement\Events\CreateDJAndOrchestraContract;
use Automas\GrantManagement\Events\CreateGrantApplicationPayment;    
use Automas\OfficeEquipmentManagement\Events\CreateOfficeMaintenanceLog;
use Automas\OfficeEquipmentManagement\Events\UpdateOfficeMaintenanceLog;
use Automas\ElderlyCare\Events\CreateElderlyCareRequest;
use Automas\ElderlyCare\Events\UpdateElderlyCareRequest;
use Automas\GameZone\Events\CreateGameRental;
use Automas\GameZone\Events\UpdateGameRental;
use Automas\GameZone\Events\CreateGameMembershipPayment;
use Automas\GameZone\Events\CreateGameFoodOrder;
use Automas\GameZone\Events\UpdateGameFoodOrder;
use Automas\LibraryManagement\Events\CreateLibraryBookFinePayment;
use Automas\NGOManagment\Events\CreateNgoDonation;
use Automas\NGOManagment\Events\UpdateNgoDonation;
use Automas\NGOManagment\Events\CreateNgoFundUtilization;
use Automas\NGOManagment\Events\UpdateNgoFundUtilization;
use Automas\FranchiseManagement\Events\CreateFranchiseAgreement;
use Automas\FranchiseManagement\Events\UpdateFranchiseAgreement;
use Automas\FranchiseManagement\Events\CreateFranchiseProfit;
use Automas\FranchiseManagement\Events\UpdateFranchiseProfit;
use Automas\PrintPressManagement\Events\CreateMachineMaintenanceRecord;
use Automas\PrintPressManagement\Events\UpdateMachineMaintenanceRecord;
use Automas\PrintPressManagement\Events\CreatePressOrder;
use Automas\PrintPressManagement\Events\UpdatePressOrder;
use Automas\PrintPressManagement\Events\CreatePressExpense;
use Automas\PrintPressManagement\Events\UpdatePressExpense;
use Automas\BakeryStore\Events\CreateBakeryExpense;
use Automas\BakeryStore\Events\UpdateBakeryExpense;
use Automas\BakeryStore\Events\CreateBakeryStoreOrder;
use Automas\BakeryStore\Events\UpdateBakeryStoreOrder;
use Automas\CoworkingSpaceManagement\Events\CreateCoworkingMembership;
use Automas\CoworkingSpaceManagement\Events\UpdateCoworkingMembership;
use Automas\CoworkingSpaceManagement\Events\CreateCoworkingBooking;
use Automas\CoworkingSpaceManagement\Events\UpdateCoworkingBooking;
use Automas\MusicInstitute\Events\CreateMusicPayment;
use Automas\MusicInstitute\Events\CreateMusicInstrumentMaintenance;
use Automas\MusicInstitute\Events\UpdateMusicInstrumentMaintenance;
use Automas\SportsClubAndAcademyManagement\Events\AssignSportsClubMembership;
use Automas\SportsClubAndAcademyManagement\Events\CreateSportsClubAndGroundOrder;
use Automas\SportsClubAndAcademyManagement\Events\UpdateSportsClubAndGroundOrder;
use Automas\EquipmentRental\Events\CreateEquipmentRentalRepair;
use Automas\EquipmentRental\Events\UpdateEquipmentRentalRepair;
use Automas\ArtShowcase\Events\CreateArtWorkOrderPayment;
use Automas\ArtShowcase\Events\UpdateArtWorkOrderPayment;
use Automas\EquipmentRental\Events\CreateEquipmentRentalBookingPayment;
use Automas\InfluencerMarketing\Events\CreateInfluencerMarketingPayoutPayment;
use Automas\DanceAcademy\Events\CreateDanceFee;
use Automas\WaterParkManagement\Events\CreateWaterParkPayment;
use Automas\WaterParkManagement\Events\CreateWaterParkClothingSales;
use Automas\WaterParkManagement\Events\UpdateWaterParkClothingSales;
use Automas\WaterParkManagement\Events\CreateWaterParkMaintenance;
use Automas\WaterParkManagement\Events\UpdateWaterParkMaintenance;
use Automas\TattooStudioManagement\Events\CreateTattooPayment;
use Automas\BloodBank\Events\CreateBloodRequestPayment;
use Automas\SolarHub\Events\CreateSolarHubMaintenance;
use Automas\SolarHub\Events\UpdateSolarHubMaintenance;
use Automas\SolarHub\Events\CreateSolarHubPayment;
use Automas\TVStudio\Events\CreateTvStudioOrder;
use Automas\TVStudio\Events\UpdateTvStudioOrder;
use Automas\Newspaper\Events\CreateNewspaperAdvertisement;
use Automas\Newspaper\Events\UpdateNewspaperAdvertisement;
use Automas\Newspaper\Events\CreateNewspaperSubscription;
use Automas\Newspaper\Events\UpdateNewspaperSubscription;
use Automas\Newspaper\Events\CreateNewspaperPayment;
use Automas\VehicleWash\Events\CreateVehicleWashBookingPayment;
use Automas\CctvSecuritySystem\Events\CreateCctvOrder;
use Automas\CctvSecuritySystem\Events\UpdateCctvOrder;
use Automas\HairAndCareStudio\Events\CreateHairCarePayment;
use Automas\PetCare\Events\CreatePetCareBookingPayment;
use Automas\PetCare\Events\CreatePetCareMembershipPayment;
use Automas\PetCare\Events\PostPetCareAdoptionRequest;
use Automas\BoutiqueAndDesignerStudio\Events\CreateBoutiquePayment;
use Automas\BoutiqueAndDesignerStudio\Events\CreateBoutiqueDamage;
use Automas\BoutiqueAndDesignerStudio\Events\CreateBoutiqueDryClean;
use Automas\InvestmentSystem\Events\CreateInvestorWithdrawPayment;
use Automas\JewelleryStoreManagement\Events\CreateJewelleryStoreRepairAndCustomOrder;
use Automas\JewelleryStoreManagement\Events\UpdateJewelleryStoreRepairAndCustomOrder;
use Automas\JewelleryStoreManagement\Events\JewelleryStoreJewelleryBookingPayments;
use Automas\JewelleryStoreManagement\Events\UpdateJewelleryStoreJewelleryBooking;
use Automas\TiffinServiceManager\Events\CreateSubscriber;
use Automas\TiffinServiceManager\Events\UpdateSubscriber;
use Automas\DJAndOrchestraManagement\Events\UpdateDJAndOrchestraContract;
use Automas\DietAndNutritionConsultant\Events\UpdateDietAppointment;
use Automas\RadiologyManagement\Events\UpdateRadiologyPayment;
use Automas\TiffinServiceManager\Events\RenewTiffinSubscriberHistory;
use Automas\Taskly\Events\PostProjectPayment;
use Automas\Taskly\Events\CreateProjectPayment;
use Automas\Taskly\Events\UpdateProjectPayment;
use Automas\GarageManagement\Events\CreateGaragePayment;
use Automas\RepairManagementSystem\Events\MakeRepairInvoicePayment;

class EventServiceProvider extends ServiceProvider
{
    protected $listen = [
        // Add your event listeners here
        DefaultData::class => [
            DataDefault::class,
        ],
        GivePermissionToRole::class => [
            GiveRoleToPermission::class,
        ],
        PostPurchaseInvoice::class => [
            PostPurchaseInvoiceListener::class,
        ],
        PostSalesInvoice::class => [
            PostSalesInvoiceListener::class,
        ],
        CreateTransfer::class => [
            CreateTransferListener::class,
        ],
        DestroyTransfer::class => [
            DestroyTransferListener::class,
        ],
        ApprovePurchaseReturn::class => [
            CreateDebitNoteFromReturn::class,
        ],
        ApproveSalesReturn::class => [
            CreateCreditNoteFromReturn::class,
        ],
        UpdateRetainerPaymentStatus::class => [
            UpdateRetainerPaymentStatusListener::class,
        ],
        ConvertSalesRetainer::class => [
            ConvertSalesRetainerListener::class,
        ],
        CreateCommissionPayment::class => [
            BankAccountFieldUpdate::class,
        ],
        UpdateCommissionPaymentStatus::class => [
            UpdateCommissionPaymentStatusListener::class,
        ],
        PaySalary::class => [
            PaySalaryListener::class,
        ],
        CreatePos::class => [
            BankAccountFieldUpdate::class,
            CreatePosListener::class,
        ],
        ApprovePosReturn::class => [
            ApprovePosReturnListener::class,
        ],
        CreateMobileServicePayment::class => [
            BankAccountFieldUpdate::class,
        ],
        UpdateMobileServicePaymentStatus::class => [
            UpdateMobileServicePaymentStatusLis::class,
        ],
        CraeteFleetBookingPayment::class => [
            BankAccountFieldUpdate::class,
        ],
        MarkFleetBookingPaymentPaid::class => [
            MarkFleetBookingPaymentPaidListener::class,
        ],
        BeautyBookingPayments::class => [
            BeautyBookingPaymentListener::class,
        ],
        MarkBeautyBookingPaymentPaid::class => [
            MarkBeautyBookingPaymentPaidListener::class,
        ],
        CreateDairyCattlePayment::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateDairyCattleExpenseTracking::class => [
            BankAccountFieldUpdate::class,
        ],
        UpdateDairyCattleExpenseTracking::class => [
            BankAccountFieldUpdate::class,
        ],
        UpdateDairyCattlePaymentStatus::class => [
            UpdateDairyCattlePaymentStatusListener::class,
        ],
        UpdateDairyCattleExpenseTrackingStatus::class => [
            UpdateDairyCattleExpenseTrackingStatusListener::class,
        ],
        CreateCateringOrderPayment::class => [
            BankAccountFieldUpdate::class,
        ],
        UpdateCateringOrderPaymentStatus::class => [
            UpdateCateringOrderPaymentStatusListener::class,
        ],
        MarkCateringExpenseTrackingAsPaid::class => [
            MarkCateringExpenseTrackingAsPaidListener::class,
        ],
        CreatePropertyPayment::class => [
            BankAccountFieldUpdate::class,
        ],
        CreatePayroll::class => [
            BankAccountFieldUpdate::class,
        ],
        UpdatePayroll::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateSalesAgentCommissionPayment::class => [
            BankAccountFieldUpdate::class,
        ],
        UpdateSalesAgentCommissionPaymentStatus::class => [
            UpdateSalesAgentCommissionPaymentStatusLis::class,
        ],
        ApproveSalesAgentCommissionAdjustment::class => [
            ApproveSalesAgentCommissionAdjustmentLis::class,
        ],
        CreateFleetExpense::class => [
            BankAccountFieldUpdate::class,
        ],
        UpdateFleetExpense::class => [
            BankAccountFieldUpdate::class,
        ],
        PostFleetExpense::class => [
            PostFleetExpenseListener::class,
        ],
        CreateCourierPayment::class => [
            BankAccountFieldUpdate::class,
        ],
        UpdateCourierPayment::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateMembershipPlanPayment::class => [
            BankAccountFieldUpdate::class,
        ],
        MembershipPlanAssigned::class => [
            MembershipPlanAssignedListener::class,
        ],
        CreateCaseExpense::class => [
            BankAccountFieldUpdate::class,
        ],
        UpdateCaseExpense::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateParkingPayment::class => [
            BankAccountFieldUpdate::class,
        ],
        ParkingBookingPayments::class => [
            ParkingBookingPaymentsListener::class,
        ],
        UpdateParkingPaymentStatus::class => [
            UpdateParkingPaymentStatusListener::class,
        ],
        CreateLaundryPayment::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateLaundryExpense::class => [
            BankAccountFieldUpdate::class,
        ],
        UpdateLaundryExpense::class => [
            BankAccountFieldUpdate::class,
            ],
        UpdateLaundryExpenseStatus::class => [
            UpdateLaundryExpenseStatusListener::class,
        ],
        UpdateLaundryPaymentStatus::class => [
            UpdateLaundryPaymentStatusListener::class,
        ],
        LaundryBookingPayments::class => [
            LaundryBookingPaymentsListener::class,
        ],
        CreateSocietyMaintenanceBillPayment::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateEventBookingPayment::class => [
            BankAccountFieldUpdate::class,
        ],
        EventBookingPayments::class => [
            EventBookingPaymentsListener::class,
        ],
        UpdateEventBookingPaymentStatus::class => [
            UpdateEventBookingPaymentStatusListener::class,
        ],
        CreateMedicalOrderPayment::class => [
            BankAccountFieldUpdate::class,
        ],
        ApproveMedicalOrderPayment::class => [
            ApproveMedicalOrderPaymentListener::class,
        ],
        CreateBeautyBookingPayment::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateFeeReceive::class => [
            BankAccountFieldUpdate::class,
        ],
        UpdateFeeReceive::class => [
            BankAccountFieldUpdate::class,
        ],
        MarkFeeReceiveAsCleared::class => [
            MarkFeeReceiveAsClearedListener::class,
        ],
        MarkCaseExpenseAsPaid::class => [
            MarkCaseExpenseAsPaidListner::class,
        ],
        CreateCaseExpense::class => [
            BankAccountFieldUpdate::class,
        ],
        UpdateCaseExpense::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateRentalMaintenance::class => [
            BankAccountFieldUpdate::class,
        ],
        UpdateRentalMaintenance::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateWarrantyClaim::class => [
            BankAccountFieldUpdate::class,
        ],
        CompleteWarrantyClaim::class => [
            CompleteWarrantyClaimListener::class,
        ],
        CreateVehicleBookingPayment::class => [
            BankAccountFieldUpdate::class,
        ],
        VehicleBookingPayments::class => [
            VehicleBookingPaymentsListener::class,
        ],
        UpdateVehicleBookingPaymentStatus::class => [
            UpdateVehicleBookingPaymentStatusListener::class,
        ],
        CreateCateringExpenseTracking::class => [
            BankAccountFieldUpdate::class,
        ],
        UpdateCateringExpenseTracking::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateHolidayzRoomBooking::class => [
            BankAccountFieldUpdate::class,
        ],
        UpdateHolidayzRoomBooking::class => [
            BankAccountFieldUpdate::class,
        ],
        HolidayzBookingPayments::class => [
            HolidayzBookingPaymentsListener::class,
        ],
        ApproveHolidayzRoomBooking::class => [
            ApproveHolidayzRoomBookingListener::class,
        ],
        CreatePettyCash::class => [
            BankAccountFieldUpdate::class,
        ],
        UpdatePettyCash::class => [
            BankAccountFieldUpdate::class,
        ],
        ApprovePettyCash::class => [
            ApprovePettyCashListener::class,
        ],
        CreatePosBillingCounter::class => [
            BankAccountFieldUpdate::class,
        ],
        UpdatePosBillingCounter::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateCourierPayment::class => [
            BankAccountFieldUpdate::class,
        ],
        UpdateCourierPayment::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateVehicletradePayment::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateServiceHistory::class => [
            BankAccountFieldUpdate::class,
        ],
        UpdateServiceHistory::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateEyewearOrder::class => [
            BankAccountFieldUpdate::class,
        ],
        UpdateEyewearOrder::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateMovieFoodOrder::class => [
            BankAccountFieldUpdate::class,
        ],
        UpdateMovieFoodOrder::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateSecurityPayment::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateTailorPayment::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateLockerPayment::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateConsultancyPayment::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateMovieBookingPayment::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateDietMemberPayment::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateDietAppointment::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateDJOrchestraEventPayment::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateDJAndOrchestraContract::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateGrantApplicationPayment::class => [
            BankAccountFieldUpdate::class,
        ], 
        CreateOfficeMaintenanceLog::class => [
            BankAccountFieldUpdate::class,
        ], 
        UpdateOfficeMaintenanceLog::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateElderlyCareRequest::class => [
            BankAccountFieldUpdate::class,
        ],
        UpdateElderlyCareRequest::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateGameRental::class => [
            BankAccountFieldUpdate::class,
        ],
        UpdateGameRental::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateGameMembershipPayment::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateGameFoodOrder::class => [
            BankAccountFieldUpdate::class,
        ],
        UpdateGameFoodOrder::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateLibraryBookFinePayment::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateNgoDonation::class => [
            BankAccountFieldUpdate::class,
        ],
        UpdateNgoDonation::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateNgoFundUtilization::class => [
            BankAccountFieldUpdate::class,
        ],
        UpdateNgoFundUtilization::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateFranchiseAgreement::class => [
            BankAccountFieldUpdate::class,
        ],
        UpdateFranchiseAgreement::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateFranchiseProfit::class => [
            BankAccountFieldUpdate::class,
        ],
        UpdateFranchiseProfit::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateMachineMaintenanceRecord::class => [
            BankAccountFieldUpdate::class,
        ],
        UpdateMachineMaintenanceRecord::class => [
            BankAccountFieldUpdate::class,
        ],
        CreatePressOrder::class => [
            BankAccountFieldUpdate::class,
        ],
        UpdatePressOrder::class => [
            BankAccountFieldUpdate::class,
        ],
        CreatePressExpense::class => [
            BankAccountFieldUpdate::class,
        ],
        UpdatePressExpense::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateBakeryExpense::class => [
            BankAccountFieldUpdate::class,
        ],
        UpdateBakeryExpense::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateBakeryStoreOrder::class => [
            BankAccountFieldUpdate::class,
        ],
        UpdateBakeryStoreOrder::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateCoworkingMembership::class => [
            BankAccountFieldUpdate::class,
        ],
        UpdateCoworkingMembership::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateCoworkingBooking::class => [
            BankAccountFieldUpdate::class,
        ],
        UpdateCoworkingBooking::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateMusicPayment::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateMusicInstrumentMaintenance::class => [
            BankAccountFieldUpdate::class,
        ],
        UpdateMusicInstrumentMaintenance::class => [
            BankAccountFieldUpdate::class,
        ],
        AssignSportsClubMembership::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateSportsClubAndGroundOrder::class => [
            BankAccountFieldUpdate::class,
        ],
        UpdateSportsClubAndGroundOrder::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateArtWorkOrderPayment::class => [
            BankAccountFieldUpdate::class,
        ],
        UpdateArtWorkOrderPayment::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateEquipmentRentalRepair::class => [
            BankAccountFieldUpdate::class,
        ],
        UpdateEquipmentRentalRepair::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateEquipmentRentalBookingPayment::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateInfluencerMarketingPayoutPayment::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateDanceFee::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateWaterParkPayment::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateWaterParkClothingSales::class => [
            BankAccountFieldUpdate::class,
        ],
        UpdateWaterParkClothingSales::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateWaterParkMaintenance::class => [
            BankAccountFieldUpdate::class,
        ],
        UpdateWaterParkMaintenance::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateTattooPayment::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateBloodRequestPayment::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateSolarHubMaintenance::class => [
            BankAccountFieldUpdate::class,
        ],
        UpdateSolarHubMaintenance::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateSolarHubPayment::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateTvStudioOrder::class => [
            BankAccountFieldUpdate::class,
        ],
        UpdateTvStudioOrder::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateNewspaperAdvertisement::class => [
            BankAccountFieldUpdate::class,
        ],
        UpdateNewspaperAdvertisement::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateNewspaperSubscription::class => [
            BankAccountFieldUpdate::class,
        ],
        UpdateNewspaperSubscription::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateNewspaperPayment::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateVehicleWashBookingPayment::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateCctvOrder::class => [
            BankAccountFieldUpdate::class,
        ],
        UpdateCctvOrder::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateHairCarePayment::class => [
            BankAccountFieldUpdate::class,
        ],
        CreatePetCareBookingPayment::class => [
            BankAccountFieldUpdate::class,
        ],
        CreatePetCareMembershipPayment::class => [
            BankAccountFieldUpdate::class,
        ],
        PostPetCareAdoptionRequest::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateBoutiquePayment::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateBoutiqueDamage::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateBoutiqueDryClean::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateInvestorWithdrawPayment::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateJewelleryStoreRepairAndCustomOrder::class => [
            BankAccountFieldUpdate::class,
        ],
        UpdateJewelleryStoreRepairAndCustomOrder::class => [
            BankAccountFieldUpdate::class,
        ],
        JewelleryStoreJewelleryBookingPayments::class => [
            BankAccountFieldUpdate::class,
        ], 
        UpdateJewelleryStoreJewelleryBooking::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateSubscriber::class => [
            BankAccountFieldUpdate::class,
        ],
        UpdateSubscriber::class => [
            BankAccountFieldUpdate::class,
        ],
        UpdateDJAndOrchestraContract::class => [
            BankAccountFieldUpdate::class,
        ],
        UpdateDietAppointment::class => [
            BankAccountFieldUpdate::class,
        ],
        UpdateRadiologyPayment::class => [
            BankAccountFieldUpdate::class,
        ],
        RenewTiffinSubscriberHistory::class => [
            BankAccountFieldUpdate::class,
        ],
        PostProjectPayment::class => [
            PostProjectPaymentListener::class,
        ],
        CreateProjectPayment::class => [
            BankAccountFieldUpdate::class,
        ],
        UpdateProjectPayment::class => [
            BankAccountFieldUpdate::class,
        ],
        CreateGaragePayment::class => [
            BankAccountFieldUpdate::class,
        ],
        MakeRepairInvoicePayment::class => [
            BankAccountFieldUpdate::class,
        ],
    ];
}
