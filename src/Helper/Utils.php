<?php

namespace App\Helper;

use Doctrine\Common\Annotations\AnnotationReader;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\PropertyInfo\Extractor\ReflectionExtractor;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Constraints\Email;

class Utils
{
    const DEFAULT_COUNTRY_CODE = 'CA';
    private $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Convert size file.
     *
     * @param mixed $size
     *
     * @return string
     */
    public function getSizeFile($size)
    {
        $size = intval($size);
        if ($size >= 1048576) {
            return number_format(($size / 1048576), 2, '.', ' ') . ' Go';
        }
        if ($size >= 1024) {
            return number_format(($size / 1024), 2, '.', ' ') . ' Mo';
        }

        return $size . ' Ko';
    }

    /**
     * To validate a mail format.
     * @param string $mail
     * @return mixed
     */
    public function validateEmail($mail = '')
    {
        $emailConstraint = new Email();

        return $this->container->get('validator')->validate(
            $mail,
            $emailConstraint
        );
    }

    public static function getIp()
    {
        $ip = '127.0.0.1';
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } elseif (!empty($_SERVER['REMOTE_ADDR'])) {
            $ip = $_SERVER['REMOTE_ADDR'];
        }

        return $ip;
    }

    /**
     * verify if ssl is activate on server.
     *
     * @return bool
     */
    public static function isSSL()
    {
        if (isset($_SERVER['HTTPS'])) {
            if ('on' == strtolower($_SERVER['HTTPS'])) {
                return true;
            }
            if ('1' == $_SERVER['HTTPS']) {
                return true;
            }
        } elseif (isset($_SERVER['SERVER_PORT']) && ('443' == $_SERVER['SERVER_PORT'])) {
            return true;
        }

        return false;
    }

    /**
     * Delete a folder and its files
     * @param String $dirPath
     */
    public static function deleteDir(string $dirPath)
    {
        if (! is_dir($dirPath)) {
            throw new \InvalidArgumentException("$dirPath must be a directory");
        }
        if (substr($dirPath, strlen($dirPath) - 1, 1) != '/') {
            $dirPath .= '/';
        }
        $files = glob($dirPath . '*', GLOB_MARK);
        foreach ($files as $file) {
            switch (true) {
                case is_dir($file):
                    self::deleteDir($file);
                    break;
                default:
                    unlink($file);
            }
        }
        rmdir($dirPath);
    }

    /**
     * @return Serializer
     */
    public static function getJsonSerializer()
    {
        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        $metadataAwareNameConverter = new MetadataAwareNameConverter($classMetadataFactory);
        $objectNormalizer = new ObjectNormalizer(
            $classMetadataFactory,
            $metadataAwareNameConverter,
            null,
            new ReflectionExtractor()
        );
        $normalizers = [
            new DateTimeNormalizer(),
            new ArrayDenormalizer(),
            $objectNormalizer,
        ];
        $encoders = [new JsonEncoder()];

        return new Serializer($normalizers, $encoders);
    }

    /**
     * Undocumented function
     *
     * @param array $groups
     * @return array
     */
    public static function setContext(array $groups): array
    {
        $context = [];
        $context['circular_reference_handler'] = function ($object) {
            return $object->getId();
        };
        $context['groups'] = $groups;

        return $context;
    }

    /**
     * @param int $strLen
     *
     * @return string
     */
    public static function generateRandomPwd($strLen = 9)
    {
        $str = "";
        for ($i = -1; $i <= 4; $i++) {
            $bytes = openssl_random_pseudo_bytes($i);
            $str .= bin2hex($bytes);
        }

        $characters = $str . '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';

        for ($i = 0; $i < $strLen; $i++) {
            $index = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$index];
        }

        return $randomString;
    }

    /**
     * @param array $array
     * @param int   $id
     * @return int
     */
    public static function findIndexOfElementInArrayById($array, $id)
    {
        for ($i = 0; $i < sizeof($array); $i++) {
            if ($array[$i]->getId() == $id) {
                return $i;
            }
        }

        return -1;
    }

    /**
     * @param int $page
     * @param int $limit
     * @return int
     */
    public static function getOffsetPage($page, $limit)
    {
        if ($page == 0) {
            return 0;
        }
        $i = 0;
        $offset = 0;
        while ($i < $page) {
            $offset += $limit;
            $i += 1;
        }

        return $offset;
    }

    public static function generateNomenclatureString()
    {
        return substr(strtolower(md5(uniqid())), 0, 20);
    }

    public static function checkFormatMail(string $email): bool
    {
        if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return true;
        }

        return false;
    }

    public static function searchText(string $search, array $fields): bool
    {
        $return = false;
        foreach ($fields as $field) {
            if (strpos(strtolower($field), strtolower($search)) !== false) {
                $return = true;
            }
        }

        return $return;
    }

    public static function generateuuidv4(int $lengthData)
    {
        $data = openssl_random_pseudo_bytes($lengthData);
        assert(strlen($data) == 16);
        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    public static function reverseQueryInUrl(string $query)
    {
        $query = str_replace('%27', '\'', $query);
        $query = str_replace('%21', '!', $query);
        $query = str_replace('%22', '"', $query);
        $query = str_replace('%23', '#', $query);
        $query = str_replace('%24', '$%22', $query);
        $query = str_replace('%25', '%', $query);
        $query = str_replace('%26', '&', $query);
        $query = str_replace('%28', '(', $query);
        $query = str_replace('%29', ')', $query);
        $query = str_replace('%2A', '*', $query);
        $query = str_replace('%2B', '+', $query);
        $query = str_replace('%2C', ',', $query);
        $query = str_replace('%2D', '-', $query);
        $query = str_replace('%2E', '.', $query);
        $query = str_replace('%2F', '/', $query);
        $query = str_replace('%3A', ':', $query);
        $query = str_replace('%3B', ';', $query);
        $query = str_replace('%3C', '<', $query);
        $query = str_replace('%3D', '=', $query);
        $query = str_replace('%3E', '>', $query);
        $query = str_replace('%3F', '?', $query);
        $query = str_replace('%40', '@', $query);
        $query = str_replace('%5B', '[', $query);
        $query = str_replace('%5C', '\\', $query);
        $query = str_replace('%09', '	', $query);
        $query = str_replace('%20', ' ', $query);
        $query = str_replace('%5D', ']', $query);
        $query = str_replace('%5E', '^', $query);
        $query = str_replace('%5F', '_', $query);
        $query = str_replace('%60', '`', $query);
        $query = str_replace('%7B', '{', $query);
        $query = str_replace('%7C', '|', $query);
        $query = str_replace('%7D', '}', $query);
        $query = str_replace('%7E', '~', $query);
        $query = str_replace('%C2%AB', '«', $query);
        $query = str_replace('%C2%BB', '»', $query);
        $query = str_replace('%C3%80', 'À', $query);
        $query = str_replace('%C3%82', 'Â', $query);
        $query = str_replace('%C3%87', 'Ç', $query);
        $query = str_replace('%C3%89', 'É', $query);
        $query = str_replace('%C3%88', 'È', $query);
        $query = str_replace('%C3%8A', 'Ê', $query);
        $query = str_replace('%C3%8B', 'Ë', $query);
        $query = str_replace('%C3%8E', 'Î', $query);
        $query = str_replace('%C3%8F', 'Ï', $query);
        $query = str_replace('%C3%94', 'Ô', $query);
        $query = str_replace('%C3%99', 'Ù', $query);
        $query = str_replace('%C3%9B', 'Û', $query);
        $query = str_replace('%C3%A0', 'à', $query);
        $query = str_replace('%C3%A2', 'â', $query);
        $query = str_replace('%C3%A7', 'ç', $query);
        $query = str_replace('%C3%A9', 'é', $query);
        $query = str_replace('%C3%A8', 'è', $query);
        $query = str_replace('%C3%AA', 'ê', $query);
        $query = str_replace('%C3%AB', 'ë', $query);
        $query = str_replace('%C3%AE', 'î', $query);
        $query = str_replace('%C3%AF', 'ï', $query);
        $query = str_replace('%C3%B4', 'ô', $query);
        $query = str_replace('%C3%B9', 'ù', $query);

        return str_replace('%C3%BB', 'û', $query);
    }

    public static function formateAddressInUrl(string $address)
    {
        $address = str_replace('\'', '%27', $address);
        $address = str_replace('!', '%21', $address);
        $address = str_replace('"', '%22', $address);
        $address = str_replace('#', '%23', $address);
        $address = str_replace('$', '%24', $address);
        $address = str_replace('%', '%25', $address);
        $address = str_replace('&', '%26', $address);
        $address = str_replace('(', '%28', $address);
        $address = str_replace(')', '%29', $address);
        $address = str_replace('*', '%2A', $address);
        $address = str_replace(',', '%2C', $address);
        $address = str_replace('-', '%2D', $address);
        $address = str_replace('.', '%2E', $address);
        $address = str_replace('/', '%2F', $address);
        $address = str_replace(':', '%3A', $address);
        $address = str_replace(';', '%3B', $address);
        $address = str_replace('<', '%3C', $address);
        $address = str_replace('=', '%3D', $address);
        $address = str_replace('>', '%3E', $address);
        $address = str_replace('?', '%3F', $address);
        $address = str_replace('@', '%40', $address);
        $address = str_replace('[', '%5B', $address);
        $address = str_replace('\\', '%5C', $address);
        $address = str_replace('	', '%09', $address);
        $address = str_replace(' ', '%20', $address);
        $address = str_replace(']', '%5D', $address);
        $address = str_replace('^', '%5E', $address);
        $address = str_replace('_', '%5F', $address);
        $address = str_replace('`', '%60', $address);
        $address = str_replace('{', '%7B', $address);
        $address = str_replace('|', '%7C', $address);
        $address = str_replace('}', '%7D', $address);
        $address = str_replace('~', '%7E', $address);
        $address = str_replace('«', '%C2%AB', $address);
        $address = str_replace('»', '%C2%BB', $address);
        $address = str_replace('À', '%C3%80', $address);
        $address = str_replace('Â', '%C3%82', $address);
        $address = str_replace('Ç', '%C3%87', $address);
        $address = str_replace('É', '%C3%89', $address);
        $address = str_replace('È', '%C3%88', $address);
        $address = str_replace('Ê', '%C3%8A', $address);
        $address = str_replace('Ë', '%C3%8B', $address);
        $address = str_replace('Î', '%C3%8E', $address);
        $address = str_replace('Ï', '%C3%8F', $address);
        $address = str_replace('Ô', '%C3%94', $address);
        $address = str_replace('Ù', '%C3%99', $address);
        $address = str_replace('Û', '%C3%9B', $address);
        $address = str_replace('à', '%C3%A0', $address);
        $address = str_replace('â', '%C3%A2', $address);
        $address = str_replace('ç', '%C3%A7', $address);
        $address = str_replace('é', '%C3%A9', $address);
        $address = str_replace('è', '%C3%A8', $address);
        $address = str_replace('ê', '%C3%AA', $address);
        $address = str_replace('ë', '%C3%AB', $address);
        $address = str_replace('î', '%C3%AE', $address);
        $address = str_replace('ï', '%C3%AF', $address);
        $address = str_replace('ô', '%C3%B4', $address);
        $address = str_replace('ù', '%C3%B9', $address);

        return str_replace('û', '%C3%BB', $address);
    }

    public static function getObjectAddressMap(string $url)
    {
        $json = file_get_contents($url);
        $data = json_decode($json);
        $objectAddressMap = null;
        $results = property_exists($data, 'results') ? $data->results : null;
        if ($results) {
            $ObjectAddress = null;
            if (count($results) > 0) {
                $formatedAddress = '';
                foreach ($results as $item) {
                    if (strlen($formatedAddress) < strlen($item->formatted_address)) {
                        $ObjectAddress = $item;
                        $formatedAddress = $item->formatted_address;
                    }
                }
            }
            if ($ObjectAddress) {
                $subpremise = null;
                $numberStreet = null;
                $nameStreet = null;
                $city = null;
                $subregion = null;
                $region = null;
                $codeCountry = null;
                $country = null;
                $codePostal = null;
                foreach ($ObjectAddress->address_components as $addressComponent) {
                    if (in_array('subpremise', $addressComponent->types)) {
                        $subpremise = $addressComponent->long_name;
                    }
                    if (in_array('street_number', $addressComponent->types)) {
                        $numberStreet = $addressComponent->long_name;
                    }
                    if (in_array('route', $addressComponent->types)) {
                        $nameStreet = $addressComponent->long_name;
                    }
                    if (in_array('locality', $addressComponent->types)) {
                        $city = $addressComponent->long_name;
                    }
                    if (in_array('administrative_area_level_2', $addressComponent->types)) {
                        $subregion = $addressComponent->long_name;
                    }
                    if (in_array('administrative_area_level_1', $addressComponent->types)) {
                        $region = $addressComponent->long_name;
                    }
                    if (in_array('country', $addressComponent->types)) {
                        $codeCountry = $addressComponent->short_name;
                        $country = $addressComponent->long_name;
                    }
                    if (in_array('postal_code', $addressComponent->types)) {
                        $codePostal = $addressComponent->long_name;
                    }
                }
                $numberStreet = $numberStreet ? $numberStreet : ($subpremise ? $subpremise : '');

                $objectAddressMap = new \stdClass();
                $objectAddressMap->subpremise = $subpremise;
                $objectAddressMap->numberStreet = $numberStreet;
                $objectAddressMap->nameStreet = $nameStreet;
                $objectAddressMap->city = $city;
                $objectAddressMap->numberStreet = $codeCountry === 'MG' ? $subpremise : $numberStreet;
                $objectAddressMap->codePostal = $codePostal;
                if ($objectAddressMap->numberStreet
                    && $objectAddressMap->nameStreet
                    && $codePostal
                    && $objectAddressMap->city
                ) {
                    $objectAddressMap->address = $objectAddressMap->numberStreet . ' ' . $objectAddressMap->nameStreet
                                                 . ', ' . $codePostal . ' ' . $objectAddressMap->city;
                } else {
                    $objectAddressMap->address = $formatedAddress;
                }

                $objectAddressMap->subregion = $subregion;
                $objectAddressMap->region = $region;
                $objectAddressMap->codeCountry = $codeCountry;
                $objectAddressMap->country = $country;

            }
        }

        return $objectAddressMap;
    }

    public static function infoCurrentDateTime(): array
    {
        $dateTime = new \DateTime();
        $dataString = $dateTime->format('Y-m-d H:i:s');

        return self::infoDateTime($dataString);
    }

    public static function infoDateTime(string $dataString): array
    {
        $return = [
            'year'                 => substr($dataString, 2, 2),
            'month'                => substr($dataString, 5, 2),
            'day'                  => substr($dataString, 8, 2),
            'fullDatetimeLastCall' => $dataString,
        ];

        $return['date'] = $return['year'] . '-' . $return['month'] . '-' . $return['day'];

        return $return;
    }

    public static function numberCurrentWeek()
    {
        return date('W');
    }

    public static function startAndEndInWeek(int $numberCurrentWeek)
    {
        $numargs = func_num_args();
        if ($numargs >= 2) {
            $year = func_get_arg(1);
        } else {
            $year = date("Y");
        }
        $fdoty = date("w", mktime(0, 0, 0, 1, 1, $year));
        $daysToSecondWeek = 8 - $fdoty;
        $week = func_get_arg(0);
        $daysToEndWeek = (($week-1) * 7) + $daysToSecondWeek;
        $daysToStartWeek = $daysToEndWeek - 6;

        $daysofweek[0] = date("Y-m-d", mktime(0, 0, 0, 1, $daysToStartWeek, $year));
        $daysofweek[1] = date("Y-m-d", mktime(0, 0, 0, 1, $daysToEndWeek, $year));

        return $daysofweek;
    }

    public static function week2str($annee, $no_semaine)
    {
        // Récup jour début et fin de la semaine
        $timeStart = strtotime("First Monday January {$annee} + ".($no_semaine - 1)." Week");
        $timeEnd   = strtotime("First Monday January {$annee} + {$no_semaine} Week -1 day");

        return [
            0 => strftime("%Y-%m-%d", $timeStart),
            1 => strftime("%Y-%m-%d", $timeEnd)
        ];
    }

    public static function getAdress($bodyRequest)
    {
        $address = null;
        if (property_exists($bodyRequest, 'adresse')) {
            if ($bodyRequest->adresse !== '') {
                $address = self::formateAddressInUrl($bodyRequest->adresse);
                $url = $_ENV['GOOGLE_MAP_SEARCH_ENDPOINT'] . '?key=' . $_ENV['GOOGLE_MAP_API_KEY'] . '&address=' . $address;
                $address = self::getObjectAddressMap($url);
            }
        }

        return $address;
    }

    public static function checkformatPhone(string $country, string $phone)
    {
        if (ctype_digit($phone) === false) {
            return false;
        }
        switch (true) {
            case $country === 'CANADA':
                return true;
            default:

        }
    }

    public static function formatePhone(string $phone)
    {
        $phone = str_replace('(', '', $phone);
        $phone = str_replace(')', '', $phone);
        $phone = str_replace('-', '', $phone);
        $phone = str_replace(' ', '', $phone);
        $phone = str_replace('.', '', $phone);
        $return = [
            'isFormatValid' => false,
            'phone'         => $phone,
        ];
        if (substr($phone, 0, 1) === '+') {
            $phone = substr($phone, 1);
        }
        if (ctype_digit($phone)) {
            // Traitement format numero CANADA
            if (strlen($phone) === 11) {
                $indicatifProvince = substr($phone, 1, 3);
                if (in_array($indicatifProvince, PhoneIndicator::$descriptions)) {
                    $return = [
                        'isFormatValid' => true,
                        'phone'         => '+' . $phone,
                    ];
                }
            } else if (strlen($phone) === 10) {
                $indicatifProvince = substr($phone, 0, 3);
                if ($indicatifProvince) {
                    $return = [
                        'isFormatValid' => true,
                        'phone'         => '+' . PhoneIndicator::INDICATOR_COUNTRY . $phone,
                    ];
                }
            }
        }

        return $return;
    }

    public static function retrievePhoneFromBalise($balise, string $site)
    {
        $phone = '';
        $text = explode('·', trim(strip_tags($balise)));
        $length = count($text);
        if ($length > 0) {
            if ($site === 'maps') {
                $phone = $text[$length-1];
                if ($phone === '') {
                    if ($length >= 2) {
                        $phone = $text[$length-2];
                        $phone = preg_replace('/[^0-9]/', '', $phone);
                        if ($phone === '') {
                            if ($length >= 3) {
                                $phone = $text[$length-3];
                                $phone = preg_replace('/[^0-9]/', '', $phone);
                                if ($phone === '') {
                                    if ($length >= 4) {
                                        $phone = $text[$length-4];
                                        $phone = preg_replace('/[^0-9]/', '', $phone);
                                        if ($phone === '') {
                                            if ($length >= 5) {
                                                $phone = $text[$length-5];
                                                $phone = preg_replace('/[^0-9]/', '', $phone);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                $phone = str_replace('Itinéraires', '', $phone);
                $phone = str_replace('Website', '', $phone);
                $phone = str_replace('Directions', '', $phone);
                $phone = str_replace(' ', '', $phone);
                $phone = preg_replace('/[^0-9]/', '', $phone);
            } else {
                $phone = $text[$length-1];
                if ($phone === '') {
                    if ($length >= 2) {
                        $phone = $text[$length-2];
                        $phoneContentDiv = explode('⋅', $phone);
                        $length2 = count($phoneContentDiv);
                        if ($length2 > 0) {
                            $phone = $phoneContentDiv[0];
                        }
                        $phone = preg_replace('/[^0-9]/', '', $phone);
                        if ($phone === '') {
                            if ($length >= 3) {
                                $phone = $text[$length-3];
                                $phoneContentDiv = explode('⋅', $phone);
                                $length2 = count($phoneContentDiv);
                                if ($length2 > 0) {
                                    $phone = $phoneContentDiv[0];
                                }
                                $phone = preg_replace('/[^0-9]/', '', $phone);
                                if ($phone === '') {
                                    if ($length >= 4) {
                                        $phone = $text[$length-4];
                                        $phoneContentDiv = explode('⋅', $phone);
                                        $length2 = count($phoneContentDiv);
                                        if ($length2 > 0) {
                                            $phone = $phoneContentDiv[0];
                                        }
                                        $phone = preg_replace('/[^0-9]/', '', $phone);
                                        if ($phone === '') {
                                            if ($length >= 5) {
                                                $phone = $text[$length-5];
                                                $phoneContentDiv = explode('⋅', $phone);
                                                $length2 = count($phoneContentDiv);
                                                if ($length2 > 0) {
                                                    $phone = $phoneContentDiv[0];
                                                }
                                                $phone = preg_replace('/[^0-9]/', '', $phone);
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                $phone = str_replace('Itinéraires', '', $phone);
                $phone = str_replace('Website', '', $phone);
                $phone = str_replace('Directions', '', $phone);
                $phone = str_replace(' ', '', $phone);
                $phoneContentDiv = explode('⋅', $phone);
                $length2 = count($phoneContentDiv);
                if ($length2 > 0) {
                    $phone = $phoneContentDiv[0];
                }
                $phone = preg_replace('/[^0-9]/', '', $phone);
            }
        }

        return $phone;
    }

    public static function formateMicroTimestamp(int $microtimestamp)
    {
        $microtimestamp = (string)$microtimestamp;
        $secondsTimestamp = substr($microtimestamp, 0, 10);
        $microRestante = str_replace($secondsTimestamp, '', $microtimestamp);

        return [
            'secondsTimestamp' => $secondsTimestamp,
            'microRestante'    => $microRestante . '000',
        ];
    }

    public static function dateTimeFromTimesmap(int $microtimestamp)
    {
        $arrayMicroTimestamp = self::formateMicroTimestamp($microtimestamp);
        return date('Y-m-d H:i:s', $arrayMicroTimestamp['secondsTimestamp'])
               . '.' . $arrayMicroTimestamp['microRestante'];
    }

    public static function bchexdec($hex)
    {
        $dec = 0;
        $len = strlen($hex);
        for ($i = 1; $i <= $len; $i++) {
            $dec = bcadd($dec, bcmul(strval(hexdec($hex[$i - 1])), bcpow('16', strval($len - $i))));
        }
        return $dec;
    }

    public static function formattedPhone(
        $phoneNumber,
        ?string $codeCountry,
        bool $isLogged = false,
        $body = null,
        LoggerInterface $phonenumberLogger = null
    ): array {
        $dataPhone = [
            'isFormatValid' => false,
            'phone'         => $phoneNumber,
        ];
        if ($phoneNumber != '') {
            $codeCountry = ($codeCountry && $codeCountry != '') ? $codeCountry : self::DEFAULT_COUNTRY_CODE;
            $next = false;
            try {
                $phoneUtil = PhoneNumberUtil::getInstance();
                $swissNumberProto = $phoneUtil->parse($phoneNumber, $codeCountry);
                $isValid = $phoneUtil->isValidNumber($swissNumberProto);
                if ($isValid) {
                    $numberInternationalFormat = $phoneUtil->format($swissNumberProto, PhoneNumberFormat::E164);
                    $dataPhone['isFormatValid'] = true;
                    $dataPhone['phone'] = $numberInternationalFormat;
                } else {
                    $next = true;
                }
            } catch (NumberParseException $e) {
                $next = true;
            }
            if ($next) {
                // On formate uniquement le format canada
                $dataPhoneNew = Utils::formatePhone($phoneNumber);
                $dataPhone['isFormatValid'] = $dataPhoneNew['isFormatValid'];
                $dataPhone['phone'] = $dataPhoneNew['phone'];
                if ($isLogged) {
                    // log le Company name, le Numéro de téléphone, le CID dans phonenumber.log
                    $data = [
                        'CompanyName' => $body->nameGMB,
                        'phoneNumber' => $body->phoneNumber,
                        'CID'         => $body->dataCID,
                    ];
                    $json = json_encode($data);
                    $phonenumberLogger->info($json);
                }
            }
        }

        return $dataPhone;
    }
}
