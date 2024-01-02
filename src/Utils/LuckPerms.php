<?php

namespace Microwin7\TextureProvider\Utils;

use Microwin7\PHPUtils\DB\SingletonConnector;
use Microwin7\TextureProvider\Request\Loader\RequestParams;

class LuckPerms
{
    private ?int $weight = null;

    public function __construct(private RequestParams $requestParams)
    {
        $this->getUserWeight();
    }
    public function getUserWeight(): int
    {
        if ($this->weight === null) {
            $this->weight = 0;
            /** @var object{uuid: string, server: string, name: string, displayname: string, weight: int, expiry: int, date: string} */
            foreach (SingletonConnector::get()->query(
                <<<SQL
            SELECT LP_USER.uuid, LP_USER.server, LP_USER.name, LP_USER.displayname, LP_USER.weight, LP_USER.expiry, LP_USER.date 
            FROM (SELECT uuid FROM luckperms_players WHERE username = ?) as LP_PLAYERS
            JOIN (SELECT USER_GROUPS.uuid, USER_GROUPS.server, LP_GROUPS.`name`, DISPLAYNAME.`displayname`, WEIGHT.weight, USER_GROUPS.expiry, from_unixtime(USER_GROUPS.expiry, '%Y-%m-%d %H:%i') as date
            FROM luckperms_groups LP_GROUPS JOIN (SELECT `name`, SUBSTR(permission, 13) AS displayname FROM luckperms_group_permissions WHERE SUBSTR(permission, 1, 12) = 'displayname.') AS DISPLAYNAME
            ON DISPLAYNAME.name = LP_GROUPS.name
            JOIN (SELECT `name`, (SUBSTR(permission, 8) * 1) AS weight FROM luckperms_group_permissions WHERE SUBSTR(permission, 1,7) = 'weight.') AS WEIGHT
            ON WEIGHT.name = LP_GROUPS.name
            JOIN (SELECT uuid, SUBSTR(permission, 7) AS group_name, `server`, expiry FROM luckperms_user_permissions WHERE SUBSTR(permission, 1,6) = 'group.') AS USER_GROUPS
            ON USER_GROUPS.group_name = LP_GROUPS.name) as LP_USER
            ON LP_USER.uuid COLLATE utf8mb4_unicode_ci = LP_PLAYERS.uuid
            ORDER BY LP_USER.weight DESC
            SQL,
                "s",
                $this->requestParams->username
            )->objects() as $r) {
                if ($r->weight > $this->weight) $this->weight = $r->weight;
            }
        }
        return $this->weight;
    }
}
