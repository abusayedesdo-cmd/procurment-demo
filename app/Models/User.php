<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    public const REQUESTER = 'requester';
    public const PROGRAM_MANAGER = 'program_manager';
    public const REVIEWER = 'reviewer';
    public const BUDGET_CHECKER = 'budget_checker';
    public const APPROVER = 'approver';
    public const FOCAL_PERSON = 'focal_person';
    public const EXECUTIVE_DIRECTOR = 'executive_director';
    public const PROCUREMENT_OFFICER = 'procurement_officer';
    public const ADMIN = 'admin';

    // Roles allowed to raise a new Purchase Requisition (document section A).
    public const PR_CREATOR_ROLES = [
        self::REQUESTER,
        self::PROGRAM_MANAGER,
    ];

    public const ROLE_LABELS = [
        self::REQUESTER            => 'Requester',
        self::PROGRAM_MANAGER      => 'Program Manager',
        self::REVIEWER             => 'Department Manager (Reviewer)',
        self::BUDGET_CHECKER       => 'Department Manager (Budget Checker)',
        self::APPROVER             => 'Department Manager (Approver)',
        self::FOCAL_PERSON         => 'Focal Person',
        self::EXECUTIVE_DIRECTOR   => 'Executive Director (ED)',
        self::PROCUREMENT_OFFICER  => 'Procurement Officer',
        self::ADMIN                => 'Admin',
    ];

    // Stage 3 (Approver) is only used by the BOQ/TOR/Design & Drawing
    // windows, which go straight from Reviewer to Approver. The PR window
    // instead runs Budget Checker -> Focal Person -> Executive Director
    // (see PrApprovalController::CHAIN), so it skips stage 3 and uses 4/5.
    public const PR_STAGE_BY_ROLE = [
        self::REQUESTER            => 0,
        self::REVIEWER              => 1,
        self::BUDGET_CHECKER        => 2,
        self::APPROVER               => 3,
        self::FOCAL_PERSON           => 4,
        self::EXECUTIVE_DIRECTOR     => 5,
    ];

    protected $fillable = [
        'name',
        'email',
        'password',
        'role_id',
        'project_id',
        'phone',
        'designation',
        'is_active',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'is_active' => 'boolean',
    ];

    public function role()
    {
        return $this->belongsTo(Role::class, 'role_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function raisedRequisitions()
    {
        return $this->hasMany(PurchaseRequisition::class, 'raised_by');
    }

    public function prApprovals()
    {
        return $this->hasMany(PrApproval::class, 'user_id');
    }

    public function committeeMemberships()
    {
        return $this->hasMany(CommitteeMember::class, 'user_id');
    }

    public function meetingsCreated()
    {
        return $this->hasMany(Meeting::class, 'created_by');
    }

    public function meetingAttendances()
    {
        return $this->hasMany(MeetingAttendance::class, 'user_id');
    }

    public function tenderOpeningsHandled()
    {
        return $this->hasMany(TenderOpening::class, 'opened_by');
    }

    public function eligibilityReportsPrepared()
    {
        return $this->hasMany(EligibilityReport::class, 'prepared_by');
    }

    public function technicalEvaluationReportsPrepared()
    {
        return $this->hasMany(TechnicalEvaluationReport::class, 'prepared_by');
    }

    public function financialEvaluationReportsPrepared()
    {
        return $this->hasMany(FinancialEvaluationReport::class, 'prepared_by');
    }

    public function comparativeStatementsPrepared()
    {
        return $this->hasMany(ComparativeStatement::class, 'prepared_by');
    }

    public function deliveryReceiptsReceived()
    {
        return $this->hasMany(DeliveryReceipt::class, 'received_by');
    }

    public function soleSourcingApprovals()
    {
        return $this->hasMany(SoleSourcingRequest::class, 'approved_by');
    }

    public function roleName(): ?string
    {
        return $this->role?->name;
    }

    public function roleLabel(): string
    {
        $name = $this->roleName();

        return self::ROLE_LABELS[$name] ?? $name ?? '—';
    }

    public function isAdmin(): bool
    {
        return $this->roleName() === self::ADMIN;
    }

    public function isProcurementOfficer(): bool
    {
        return $this->roleName() === self::PROCUREMENT_OFFICER;
    }

    public function isPrCreator(): bool
    {
        return in_array($this->roleName(), self::PR_CREATOR_ROLES, true);
    }

    public function isInPrChain(): bool
    {
        $name = $this->roleName();

        return $name !== null && array_key_exists($name, self::PR_STAGE_BY_ROLE);
    }

    public function prStage(): ?int
    {
        return self::PR_STAGE_BY_ROLE[$this->roleName()] ?? null;
    }

    public function canManageProcurement(): bool
    {
        return $this->isAdmin() || $this->isProcurementOfficer();
    }
}