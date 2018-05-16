<?php
/**
$sql =<<<eod
PRAGMA foreign_keys=off;

BEGIN TRANSACTION;

ALTER TABLE "main"."company_detail_info" RENAME TO "_company_detail_info_old_20180515";

DROP INDEX "main"."saicSysNoIDX";

CREATE TABLE "main"."company_detail_info" (
     "id" integer NOT NULL PRIMARY KEY AUTOINCREMENT,
     "company" text NOT NULL,
     "saicSysNo" text NOT NULL,
     "name" text,
     "qq" text,
     "registery_price" text,
     "company_email" text,
     "company_phone" text,
     "company_address" text,
     "company_status" text,
     "info" text,
     "web" text,
     "reg_url" text,
     "reg_name" text,
     "reg_organ" text,
     "reg_street" text,
     "reg_city" text,
     "reg_state" text,
     "reg_post" text,
     "reg_country" text,
     "reg_phone" text,
     "reg_email" text,
     "admin_phone" text,
     "admin_email" text,
     "tech_phone" text,
     "tech_email" text,
     "lost_credit" integer DEFAULT 0,
     "status" text,
     "state" text,
    CONSTRAINT "saicSysNoFK" FOREIGN KEY ("saicSysNo") REFERENCES "company_grasp_list" ("saicSysNo")
);

INSERT INTO "main".sqlite_sequence (name, seq) VALUES ("company_detail_info", '1014');

INSERT INTO "main"."company_detail_info" ("id", "company", "saicSysNo", "name", "qq", "registery_price", "company_email", "company_phone", "company_address", "company_status", "info", "web", "reg_url", "reg_name", "reg_organ", "reg_street", "reg_city", "reg_state", "reg_post", "reg_country", "reg_phone", "reg_email", "admin_phone", "admin_email", "tech_phone", "tech_email", "lost_credit", "status") SELECT "id", "company", "saicSysNo", "name", "qq", "registery_price", "company_email", "company_phone", "company_address", "company_status", "info", "web", "reg_url", "reg_name", "reg_organ", "reg_street", "reg_city", "reg_state", "reg_post", "reg_country", "reg_phone", "reg_email", "admin_phone", "admin_email", "tech_phone", "tech_email", "lost_credit", "status" FROM "main"."_company_detail_info_old_20180515";

CREATE UNIQUE INDEX "main"."saicSysNoIDX" ON company_detail_info ("saicSysNo" ASC);

COMMIT;

PRAGMA foreign_keys=on;
eod;
$sth = \Lib\Sqlite::sqLite()->query($sql);
$sth->execute();
*/

$province = [
    'beijing' => 110000,
    'tianjing' => 120000,
    'hebei' => 130000,
    'shanxi' => 140000,
    'neimenggu' => 150000,
    'liaoning' => 210000,
    'jilin' => 220000,
    'heilongjiang' => 230000,
    'shanghai' => 310000,
    'jiangsu' => 320000,
    'zhejiang' => 330000,
    'anhui' => 340000,
    'fujian' => 350000,
    'jiangxi' => 360000,
    'shandong' => 370000,
    'henan' => 410000,
    'hubei' => 420000,
    'hunan' => 430000,
    'guangdong' => 440000,
    'guangxi' => 450000,
    'hainan' => 460000,
    'chongqing' => 500000,
    'sichuan' => 510000,
    'guizhou' => 520000,
    'yunnan' => 530000,
    'xizang' => 540000,
    'shanxi' => 610000,
    'ganshu' => 620000,
    'qinghai' => 630000,
    'ningxia' => 640000,
    'xinjiang' => 650000,
    'taiwan' => 710000,
    'xiangguang' => 810000,
    'aomen' => 820000,
    'diaoyudao' => 900000
];

$init = [
    // 信息
    'page' => 'collect/resource/mohusou/%s/page',
    'name' => 'collect/resource/mohusou/%s/id',
];

foreach ($province as $name => $id) {
    foreach ($init as $idx => $tpl) {
        $value = $idx == 'page' ? 1 : $id ;
        \Lib\Tenf::getModel('config')::addConfig(sprintf($tpl, $name), $value);
    }
}
