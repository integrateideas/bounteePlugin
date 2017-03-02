<?php
use Migrations\AbstractSeed;

/**
 * AwardTypes seed.
 */
class AwardTypesSeed extends AbstractSeed
{
    /**
     * Run Method.
     *
     * Write your database seeder using this method.
     *
     * More information on writing seeds is available here:
     * http://docs.phinx.org/en/latest/seeding.html
     *
     * @return void
     */
    public function run()
    {
        $data = [

                    [
                        'name' => 'PromotionsAwards',
                    ],
                    [
                        'name' => 'ManualAwards',
                    ],
                    [
                        'name' => 'TierAwards',
                    ],
                    [
                        'name' => 'SurveyAwards',
                    ],
                    [
                        'name' => 'MilestoneLevelAwards',
                    ],
                    [
                        'name' => 'GiftCouponAwards',
                    ]


        ];

        $table = $this->table('award_types');
        $table->insert($data)->save();
    }
}
