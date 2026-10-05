<?php

namespace Database\Seeders;

use App\Models\Branch;
use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class CentralPermissionSeeder extends Seeder
{
    /**
     * Create the initial roles and permissions.
     *
     * @return void
     */
    public function run()
    {
        // Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // All Users
        $dashboardPermission = [
            'dashboard read',
        ];

        // System Admin
        $announcementPermission = [
            'announcement read',
            'announcement create',
            'announcement update',
            'announcement delete',
            'announcement status',
        ];

        // System Admin
        $maintenancePermission = [
            'maintenance read',
            'maintenance create',
            'maintenance update',
        ];

        // System Admin
        $rolePermission = [
            'role read',
            'role create',
            'role update',
            'role delete',
        ];

        // System Admin
        $tenantPermission = [
            'tenant read',
            'tenant create',
            'tenant update',
            'tenant delete',
        ];

        // Super Admin create System Admin, while System Admin create Agency Admin
        // Agency Admin will create for investigator, report admin and data request admin
        $userPermission = [
            'user read',
            'user create',
            'user update',
            'user delete',
        ];

        $reportPermission = [
            'report read', // All Users
            'report create', // Report Admin
            'report update', // Report Admin
            'report delete', // Report Admin
        ];

        $fileManagementPermission = [
            'file management read',
            'file management create',
            'file management update',
            'file management delete',
        ];

        // All Users
        $eventPermission = [
            'event calendar read',
            'event calendar create',
            'event calendar update',
            'event calendar delete',
        ];


        $dataRequestPermission = [
            'data request read', // Data Request Admin && Investigator
            'data request create', // Investigator
            'data request update', // Investigator
            'data request approval', // Data Request Admin
        ];

        // Data Request Admin
        $dataRequestHistoryPermission = [
            'data request history read',
            'data request history create',
            'data request history delete',
        ];

        // Investigator
        $integratedSearchPermission = [
            'integrated search read',
            'integrated search generate file',
            'integrated search save',
            'integrated search show',
        ];

        // Investigator
        $intelligenceManagementPermission = [
            'intelligence management read',
            'intelligence management create',
            'intelligence management update',
            'intelligence management delete',
            'intelligence management share',
        ];

        // Investigator
        $notePermission = [
            'note read',
            'note create',
            'note update',
            'note delete',
        ];

        // Investigator
        $profilingPermission = [
            'profiling read',
            'profiling create',
            'profiling update',
            'profiling delete',
        ];

        // System Admin
        $userManualPermission = [
            'user manual read',
            'user manual create',
            'user manual update',
            'user manual delete',
        ];

        // Superadmin
        $menuPermission = [
            'menu read',
            'menu create',
            'menu update',
            'menu delete',
        ];

        // System Admin
        $faqPermission = [
            'faq read',
            'faq create',
            'faq update',
            'faq delete',
        ];

        // System Admin
        $faqCategoryPermission = [
            'faq category read',
            'faq category create',
            'faq category update',
            'faq category delete',
        ];

        $inquiryPermission = [
            'inquiry read', // All Users
            'inquiry create', // All Users
            'inquiry update', // System Admin
            'inquiry delete', // All Users
        ];

        // All Users
        $forumPermission = [
            'forum read',
            'forum create',
            'forum update',
            'forum delete',
            'forum reply',
        ];

        // All Users
        $chatPermission = [
            'chat read',
            'chat create',
        ];

        $arrayOfPermissionNames = array_merge(
            $dashboardPermission,
            $announcementPermission,
            $maintenancePermission,
            $rolePermission,
            $tenantPermission,
            $userPermission,
            $reportPermission,
            $fileManagementPermission,
            $eventPermission,
            $dataRequestPermission,
            $dataRequestHistoryPermission,
            $integratedSearchPermission,
            $intelligenceManagementPermission,
            $notePermission,
            $profilingPermission,
            $userManualPermission,
            $menuPermission,
            $faqPermission,
            $faqCategoryPermission,
            $inquiryPermission,
            $forumPermission,
            $chatPermission
        );

        $arraySuperAdmin = array_merge($userPermission, $menuPermission, $dashboardPermission, $eventPermission, $chatPermission, $forumPermission, $fileManagementPermission, $rolePermission, ['inquiry read', 'inquiry create', 'inquiry delete']);

        $arraySysAdmin = array_merge($dashboardPermission, $userPermission, $tenantPermission, $inquiryPermission, $announcementPermission, $maintenancePermission, $rolePermission, $userManualPermission, $faqPermission, $faqCategoryPermission, $forumPermission, $chatPermission, $eventPermission, $fileManagementPermission);

        $arrayAgencyAdmin = array_merge($userPermission, $dashboardPermission, $eventPermission, $forumPermission, $chatPermission, $fileManagementPermission, ['role read', 'report read', 'inquiry read','inquiry create', 'inquiry delete']);

        $arrayInvestigator = array_merge($integratedSearchPermission, $eventPermission, $intelligenceManagementPermission, $notePermission, $profilingPermission, $dashboardPermission, $forumPermission, $chatPermission, $fileManagementPermission, ['data request read', 'data request create', 'data request update', 'report read', 'inquiry read', 'inquiry create', 'inquiry delete']);

        $arrayRequestDataAdmin = array_merge($eventPermission, $dataRequestHistoryPermission, $dashboardPermission, $forumPermission, $chatPermission, $fileManagementPermission, ['data request read', 'data request approval', 'report read', 'inquiry read', 'inquiry create', 'inquiry delete']);

        $arrayReportAdmin = array_merge($eventPermission, $dashboardPermission, $reportPermission, $forumPermission, $chatPermission, $fileManagementPermission, ['inquiry read', 'inquiry create', 'inquiry delete']);

        foreach ($arrayOfPermissionNames as $permission) {
            Permission::create(['name' => $permission,  'guard_name' => 'web']);
        }

        // create roles and assign existing permissions
        $sysAdmin = Role::create(['name' => 'system admin']);
        $sysAdmin->givePermissionTo($arraySysAdmin);

        $superAdmin = Role::create(['name' => 'super admin']);
        $superAdmin->givePermissionTo($arraySuperAdmin);

        $agencyAdmin = Role::create(['name' => 'agency admin']);
        $agencyAdmin->givePermissionTo($arrayAgencyAdmin);

        $reportAdmin = Role::create(['name' => 'report admin']);
        $reportAdmin->givePermissionTo($arrayReportAdmin);

        $investigator = Role::create(['name' => 'investigator']);
        $investigator->givePermissionTo($arrayInvestigator);

        $dataRequestAdmin = Role::create(['name' => 'data request admin']);
        $dataRequestAdmin->givePermissionTo($arrayRequestDataAdmin);

        $staff = Role::create(['name' => 'staff']);

        // create demo users (Start)
        // super admin
        $superAdminUser = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@cims.com',
        ]);

        $branch = Branch::create([
            'name' => 'Head Office',
        ]);

        Profile::create([
            'user_id' => $superAdminUser->id,
            'branch_id' => $branch->id,
            'type_id' => 1,
            'id_no' => '870416078223',
            'gender' => 1,
            'designation' => 'Super Admin',
            'dep_unit' => 'IT',
            'phone_no' => '0376521123',
            'mobile_no' => '0176523023',
            'dob' => '1987-04-16',
        ]);

        $superAdminUser->assignRole($superAdmin);
        $superAdminUser->givePermissionTo($arraySuperAdmin);

        // system admin
        $sysAdminUser = User::factory()->create([
            'name' => 'System Admin',
            'email' => 'sysadmin@cims.com',
        ]);

        Profile::create([
            'user_id' => $sysAdminUser->id,
            'branch_id' => $branch->id,
            'type_id' => 1,
            'id_no' => '980123102332',
            'gender' => 1,
            'designation' => 'System Admin',
            'dep_unit' => 'IT',
            'phone_no' => '0323456789',
            'mobile_no' => '0123456789',
            'dob' => '1998-01-23',
        ]);

        $sysAdminUser->assignRole($sysAdmin);
        $sysAdminUser->givePermissionTo($arraySysAdmin);

        // agency admin
        $agencyAdminUser = User::factory()->create([
            'name' => 'Agency Admin',
            'email' => 'agencyadmin@cims.com',
        ]);

        $agencyAdminUser->assignRole($agencyAdmin);
        $agencyAdminUser->assignRole($staff);
        $agencyAdminUser->givePermissionTo($arrayAgencyAdmin);

        // report admin
        $reportAdminUser = User::factory()->create([
            'name' => 'Report Admin',
            'email' => 'reportadmin@cims.com',
        ]);

        $reportAdminUser->assignRole($reportAdmin);
        $reportAdminUser->assignRole($staff);
        $reportAdminUser->givePermissionTo($arrayReportAdmin);

        // investigator
        $investigatorUser = User::factory()->create([
            'name' => 'Investigator',
            'email' => 'investigator@cims.com',
        ]);

        $investigatorUser->assignRole($investigator);
        $investigatorUser->assignRole($staff);
        $investigatorUser->givePermissionTo($arrayInvestigator);

        // data request admin
        $dataRequestUser = User::factory()->create([
            'name' => 'Data Request Admin',
            'email' => 'datarequestadmin@cims.com',
        ]);

        $dataRequestUser->assignRole($dataRequestAdmin);
        $dataRequestUser->assignRole($staff);
        $dataRequestUser->givePermissionTo($arrayRequestDataAdmin);

        // create demo users (End)
    }
}
