<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ContentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // About us page
        DB::table('aboutuspage')->insertOrIgnore([
            'about_id' => 1,
            'title' => 'About Us',
            'description' => '<p><strong>About Us</strong><br />
GoGrocer is an online Delivery Mobile App as a Service. We are committed to nurturing a neutral platform and are helping food establishments maintain high standards through Hyperpure. Food Hygiene Ratings is a coveted mark of quality among our restaurant partners.</p>',
        ]);

        // Terms and conditions page
        DB::table('termspage')->insertOrIgnore([
            'terms_id' => 1,
            'title' => 'Terms & Condition',
            'description' => '<table cellspacing="0" id="datatables" style="width:100%">
    <tbody>
        <tr>
            <td>&nbsp;</td>
            <td>
            <p><strong>Terms and Conditions</strong></p>
            
            <p>Last Updated: 05 May 2021</p>
            
            <p>&nbsp;</p>
            </td>
        </tr>
    </tbody>
</table>',
        ]);

        // App notice
        DB::table('app_notice')->insertOrIgnore([
            'app_notice_id' => 1,
            'status' => 1,
            'notice' => 'This is Test Notice. Admin can change it.',
        ]);
    }
}
