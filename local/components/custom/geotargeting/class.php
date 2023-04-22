<?

class GeotargetingComponent extends CBitrixComponent
{
    private  $DEFAULT_DOMAIN_SITE1 = "moskva.site1-ru.ru";
    private  $DEFAULT_DOMAIN_SITE2 = "moskva.site2-ru.ru";

    /**
     * Получаем корректный домен для работы с инфоблоком городов
     */
    private function getFilter(): array
    {
        $defaultDomainSite1 = $this->DEFAULT_DOMAIN_SITE1;
        $defaultDomainSite2 = $this->DEFAULT_DOMAIN_SITE2;

        $arFilter = [
            "IBLOCK_ID" => SiteHandlers::getIblockIdByCode("cities", "service"),
        ];

        if (strpos($_SERVER["HTTP_HOST"], 'site1') !== false) {
            // если в домене есть site1
            if ($_SERVER["HTTP_HOST"] == $defaultDomainSite1) {
                $arFilter["=PROPERTY_SITE1_DOMAIN"] = $defaultDomainSite1;
            } else {
                $arFilter["=PROPERTY_SITE1_DOMAIN"] = $_SERVER["HTTP_HOST"];
            }
        } else {
            if (strpos($_SERVER["HTTP_HOST"], 'site2') !== false) {
                // если в домене есть site2
                if ($_SERVER["HTTP_HOST"] == $defaultDomainSite2) {
                    $arFilter["=PROPERTY_SITE1_DOMAIN"] = $defaultDomainSite2;
                } else {
                    $arFilter["=PROPERTY_SITE2_DOMAIN"] = $_SERVER["HTTP_HOST"];
                }
            } else {
                // если в домене нет ни site1, ни site2
                $arFilter = [];
            }
        }
        return $arFilter;
    }

    /**
     * Получаем массивы фильтра и склонения города для добавления в сессию.
     */
    private function getSessionVariables(): array
    {
        $city = $this->getCity();

        $arraySessionVariables = [
            'PROPERTY_CITY' => $city["ID"],
            'Declination' => [
                '#CITY_NOMINATIVE#' => $city["NAME"],
                '#CITY_GENITIVE#' => $city["PROPERTY_PREPOSITIONAL_VALUE"],
                '#CITY_PREPOSITIONAL#' => $city["PROPERTY_GENITIVE_VALUE"],
            ]
        ];
        return $arraySessionVariables;
    }

    /**
     * Получаем массив всех городов
     */
    private function getCity(): array
    {
        $result = [];
        $filter = $this->getFilter();
        $getCity = CIBlockElement::GetList(
            [],
            [
                $filter
            ],
            false,
            false,
            [
                "ID",
                "IBLOCK_ID",
                "CODE",
                "NAME",
                "PROPERTY_PREPOSITIONAL",
                "PROPERTY_GENITIVE",
                "PROPERTY_SITE2_DOMAIN",
                "PROPERTY_SITE1_DOMAIN",
            ],
        );
        if ($arCity = $getCity->fetch()) {
            $result = $arCity;
        }

        return $result;
    }

    /**
     * Добавление в сессию переменных для склонения города(в заголовках)
     * и
     * фильтра для вывода элементов
     */
    private function defineSessionVariables(array $variables = []): void
    {
        if (empty($variables)) {
            $variables = $this->getSessionVariables();
        }

        $session = &$_SESSION;
        foreach ($variables as $name => $value) {
            if (!$session[$name]) {
                $session[$name] = $value;
            }
        }
    }

    public function executeComponent()
    {
        $this->defineSessionVariables();
    }
}
