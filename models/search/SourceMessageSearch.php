<?php

namespace uran1980\yii\modules\i18n\models\search;

use Yii;
use yii\data\ActiveDataProvider;
use yii\helpers\ArrayHelper;
use yii\console\Exception;
use yii\helpers\Console;
use yii\helpers\FileHelper;
use yii\helpers\VarDumper;
use yii\i18n\GettextPoFile;
use yii\helpers\Json;
use uran1980\yii\modules\i18n\Module;
use uran1980\yii\modules\i18n\models\SourceMessage;
use Zelenin\yii\modules\I18n\models\Message;

class SourceMessageSearch extends SourceMessage
{
    const STATUS_TRANSLATED     = 'translated';
    const STATUS_NOT_TRANSLATED = 'not-translated';
    const STATUS_ALL            = 'all';
    const STATUS_DELETED        = 'deleted';

    public $status;

    /**
     * @var SourceMessageSearch
     */
    protected static $_instance = null;

    /**
     * @var boolean whether to enable ANSI color in the output.
     * If not set, ANSI color will only be enabled for terminals that support it.
     */
    public $color;

    /**
     * @var array
     */
    protected $locations = [];

    /**
     * @var array
     */
    protected $config = [];

    /**
     * @var string
     */
    public $translation;

    /**
     * @return SourceMessageSearch
     */
    public static function getInstance()
    {
        if ( null === self::$_instance )
            self::$_instance = new self();

        return self::$_instance;
    }

    public function init()
    {
        if (!Yii::$app->has('i18n')) {
            throw new Exception('The i18n component does not exist');
        }

        $i18n = Yii::$app->i18n;
        $this->config = [
            'languages'             => $i18n->languages,
            'sourcePath'            => (is_string($i18n->sourcePath) ? [$i18n->sourcePath] : $i18n->sourcePath),
            'translator'            => $i18n->translator,
            'sort'                  => $i18n->sort,
            'removeUnused'          => $i18n->removeUnused,
            'only'                  => $i18n->only,
            'except'                => $i18n->except,
            'format'                => $i18n->format,
            'db'                    => $i18n->db,
            'messagePath'           => $i18n->messagePath,
            'overwrite'             => $i18n->overwrite,
            'catalog'               => $i18n->catalog,
            'messageTable'          => $i18n->messageTable,
            'sourceMessageTable'    => $i18n->sourceMessageTable,
        ];
    }

    /**
     * Returns the validation rules for attributes.
     *
     * Validation rules are used by [[validate()]] to check if attribute values are valid.
     * Child classes may override this method to declare different validation rules.
     *
     * Each rule is an array with the following structure:
     *
     * ~~~
     * [
     *     ['attribute1', 'attribute2'],
     *     'validator type',
     *     'on' => ['scenario1', 'scenario2'],
     *     ...other parameters...
     * ]
     * ~~~
     *
     * where
     *
     *  - attribute list: required, specifies the attributes array to be validated, for single attribute you can pass string;
     *  - validator type: required, specifies the validator to be used. It can be a built-in validator name,
     *    a method name of the model class, an anonymous function, or a validator class name.
     *  - on: optional, specifies the [[scenario|scenarios]] array when the validation
     *    rule can be applied. If this option is not set, the rule will apply to all scenarios.
     *  - additional name-value pairs can be specified to initialize the corresponding validator properties.
     *    Please refer to individual validator class API for possible properties.
     *
     * A validator can be either an object of a class extending [[Validator]], or a model class method
     * (called *inline validator*) that has the following signature:
     *
     * ~~~
     * // $params refers to validation parameters given in the rule
     * function validatorName($attribute, $params)
     * ~~~
     *
     * In the above `$attribute` refers to currently validated attribute name while `$params` contains an array of
     * validator configuration options such as `max` in case of `string` validator. Currently validate attribute value
     * can be accessed as `$this->[$attribute]`.
     *
     * Yii also provides a set of [[Validator::builtInValidators|built-in validators]].
     * They each has an alias name which can be used when specifying a validation rule.
     *
     * Below are some examples:
     *
     * ~~~
     * [
     *     // built-in "required" validator
     *     [['username', 'password'], 'required'],
     *     // built-in "string" validator customized with "min" and "max" properties
     *     ['username', 'string', 'min' => 3, 'max' => 12],
     *     // built-in "compare" validator that is used in "register" scenario only
     *     ['password', 'compare', 'compareAttribute' => 'password2', 'on' => 'register'],
     *     // an inline validator defined via the "authenticate()" method in the model class
     *     ['password', 'authenticate', 'on' => 'login'],
     *     // a validator of class "DateRangeValidator"
     *     ['dateRange', 'DateRangeValidator'],
     * ];
     * ~~~
     *
     * Note, in order to inherit rules defined in the parent class, a child class needs to
     * merge the parent rules with child rules using functions such as `array_merge()`.
     *
     * @return array validation rules
     * @see scenarios()
     */
    public function rules()
    {
        return [
            ['category', 'safe'],
            ['message', 'safe'],
            ['status', 'safe'],
            // for filter with relation table
            // @see http://www.yiiframework.com/wiki/621/filter-sort-by-calculated-related-fields-in-gridview-yii-2-0/
            ['translation', 'safe'],

        ];
    }

    /**
     * @param array|null $params
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = SourceMessage::find();
        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => [
                    'id' => SORT_DESC,
                ],
            ],
        ]);

        // check and populate params
        if (!($this->load($params) && $this->validate())) {
            $query->joinWith(['messages']);
            return $dataProvider;
        }

        if ($this->status == static::STATUS_TRANSLATED) {
            $query->joinWith(['messages']);
            $query->translated();
        }
        if ($this->status == static::STATUS_NOT_TRANSLATED) {
            $query->joinWith(['messages']);
            $query->notTranslated();
        }
        if ( $this->status == static::STATUS_DELETED ) {
            $query->joinWith(['messages']);
            $query->deleted();
        }

        // search with related table
        // @see http://www.yiiframework.com/wiki/621/filter-sort-by-calculated-related-fields-in-gridview-yii-2-0/
        if ( !empty($this->translation) ) {
            $query->joinWith(['messages' => function ($q) {
                $q->where(['like', Message::tableName() . '.translation', $this->translation]);
            }]);
        }

        $query
            ->andFilterWhere(['like', 'category', $this->category])
            ->andFilterWhere(['like', 'message', $this->message])
        ;

        return $dataProvider;
    }

    /**
     * @param int $id
     * @return array
     */
    public static function getStatus($id = null)
    {
        $statuses = [
            self::STATUS_TRANSLATED => Module::t('Translated'),
            self::STATUS_NOT_TRANSLATED => Module::t('Not translated'),
        ];
        if ($id !== null) {
            return ArrayHelper::getValue($statuses, $id, null);
        }
        return $statuses;
    }

    /**
     * @param array $item
     * @return string
     */
    public static function isActiveTranslation($item)
    {
        $output = '';                                                           // default

        $params = Yii::$app->request->getQueryParams();
        unset($params['page'], $params['sort']);
        if (isset($item['url']) && is_array($item['url']) && isset($item['url'][0])) {
            $route = $item['url'][0];
            if ($route[0] !== '/' && Yii::$app->controller) {
                $route = Yii::$app->controller->module->getUniqueId() . '/' . $route;
            }
            if (ltrim($route, '/') === Yii::$app->controller->getRoute()) {
                if ( empty($params) && count($item['url']) == 1 )
                    return ' active ';
            }
            unset($item['url']['#']);
            if ( isset($params['SourceMessageSearch'], $params['SourceMessageSearch']['status']) ) {
                if ( count($item['url']) > 1 ) {
                    foreach ( $item['url'] as $name => $value ) {
                        if ( $params['SourceMessageSearch']['status'] == $value ) {
                            return ' active ';
                        } elseif ( $name == 'SourceMessageSearch'
                                   && isset($item['current'])
                                   && $params['SourceMessageSearch']['status'] == $item['current'] )
                        {
                            return ' active ';
                        }
                    }
                } elseif ( empty($params['SourceMessageSearch']['status']) ) {
                    return ' active ';
                }
            } elseif ( isset($item['current']) && $item['current'] == self::STATUS_ALL ) {
                return ' active ';
            }
        }

        return $output;
    }

    /**
     * @return array
     */
    public function attributeLabels()
    {
        return [
            'id'        => 'ID',
            'category'  => Module::t('Category'),
            'message'   => Module::t('Message'),
            'status'    => Module::t('Translation status'),
            'location'  => Module::t('Location'),
        ];
    }

    /**
     * Deletes all translations values from cache.
     *
     * @return boolean whether the flush operation was successful.
     */
    public static function cacheFlush()
    {
        foreach ( self::getCategories() as $category ) {
            foreach ( Yii::$app->i18n->languages as $language ) {
                Yii::$app->cache->delete([
                    'yii\i18n\DbMessageSource',
                    $category,
                    $language,
                ]);
            }
        }

        return true;
    }

    /**
     * Extracts messages to be translated from source code.
     *
     * This command will search through source code files and extract
     * messages that need to be translated in different languages.
     *
     * @throws Exception on failure.
     * @return array
     */
    public function extract()
    {
        if (!isset($this->config['sourcePath'], $this->config['languages'])) {
            throw new Exception('The configuration must specify "sourcePath" and "languages".');
        }

        foreach ($this->config['sourcePath'] as $sourcePath) {
            if (!is_dir($sourcePath)) {
                throw new Exception("The source path {$sourcePath} is not a valid directory.");
            }
        }

        if (empty($this->config['format']) || !in_array($this->config['format'], ['php', 'po', 'db'])) {
            throw new Exception('Format should be either "php", "po" or "db".');
        }
        if (in_array($this->config['format'], ['php', 'po'])) {
            if (!isset($this->config['messagePath'])) {
                throw new Exception('The configuration file must specify "messagePath".');
            } elseif (!is_dir($this->config['messagePath'])) {
                throw new Exception("The message path {$this->config['messagePath']} is not a valid directory.");
            }
        }
        if (empty($this->config['languages'])) {
            throw new Exception("Languages cannot be empty.");
        }

        $files = [];
        foreach ( $this->config['sourcePath'] as $sourcePath ) {
            $files = array_merge(
                array_values($files),
                array_values(FileHelper::findFiles(realpath($sourcePath), $this->config))
            );
        }

        $messages = [];
        foreach ($files as $file) {
            $messages = array_merge_recursive($messages, $this->extractMessages($file, $this->config['translator']));
        }
        if (in_array($this->config['format'], ['php', 'po'])) {
            foreach ($this->config['languages'] as $language) {
                $dir = $this->config['messagePath'] . DIRECTORY_SEPARATOR . $language;
                if (!is_dir($dir)) {
                    @mkdir($dir);
                }
                if ($this->config['format'] === 'po') {
                    $catalog = isset($this->config['catalog']) ? $this->config['catalog'] : 'messages';
                    return $this->saveMessagesToPO($messages, $dir, $this->config['overwrite'], $this->config['removeUnused'], $this->config['sort'], $catalog);
                } else {
                    return $this->saveMessagesToPHP($messages, $dir, $this->config['overwrite'], $this->config['removeUnused'], $this->config['sort']);
                }
            }
        } elseif ($this->config['format'] === 'db') {
            $db = \Yii::$app->get(isset($this->config['db']) ? $this->config['db'] : 'db');
            if (!$db instanceof \yii\db\Connection) {
                throw new Exception('The "db" option must refer to a valid database application component.');
            }
            $sourceMessageTable = isset($this->config['sourceMessageTable']) ? $this->config['sourceMessageTable'] : '{{%source_message}}';
            $messageTable = isset($this->config['messageTable']) ? $this->config['messageTable'] : '{{%message}}';
            return $this->saveMessagesToDb(
                $messages,
                $db,
                $sourceMessageTable,
                $messageTable,
                $this->config['removeUnused'],
                $this->config['languages']
            );
        }
    }

    /**
     * Saves messages to database
     *
     * @param array $messages
     * @param \yii\db\Connection $db
     * @param string $sourceMessageTable
     * @param string $messageTable
     * @param boolean $removeUnused
     * @param array $languages
     */
    public function saveMessagesToDb($messages, $db, $sourceMessageTable, $messageTable, $removeUnused, $languages)
    {
        $q = new \yii\db\Query;
        $current = [];

        foreach ($q->select(['id', 'category', 'message'])->from($sourceMessageTable)->all() as $row) {
            $current[$row['category']][$row['id']] = $row['message'];
        }

        $new = [];
        $obsolete = [];

        foreach ($messages as $category => $msgs) {
            $msgs = array_unique($msgs);

            if (isset($current[$category])) {
                $new[$category] = array_diff($msgs, $current[$category]);
                $obsolete += array_diff($current[$category], $msgs);
            } else {
                $new[$category] = $msgs;
            }
        }

        foreach (array_diff(array_keys($current), array_keys($messages)) as $category) {
            $obsolete += $current[$category];
        }

        if (!$removeUnused) {
            foreach ($obsolete as $pk => $m) {
                if (mb_substr($m, 0, 2) === '@@' && mb_substr($m, -2) === '@@') {
                    unset($obsolete[$pk]);
                }
            }
        }

        $obsolete = array_keys($obsolete);
        $this->stdout("Inserting new messages...");
        $savedFlag = false;
        $columnNames = $db->getTableSchema($sourceMessageTable)->columnNames;
        $hasLocationColumn = in_array('location', $columnNames) ?: false;

        foreach ($new as $category => $msgs) {
            foreach ($msgs as $m) {
                $savedFlag  = true;
                $msgHash    = md5($m);
                $sourceMessageData = [
                    'category'  => $category,
                    'message'   => $m,
                ];
                if ( true === $hasLocationColumn ) {
                    $sourceMessageData['location']  = $this->extractLocations($category, $m);
                    $sourceMessageData['hash']      = $msgHash;
                }
                $db->createCommand()
                    ->insert($sourceMessageTable, $sourceMessageData)
                    ->execute()
                ;
                $lastID = ($db->driverName == 'pgsql')
                        ? $db->getLastInsertID($sourceMessageTable . '_id_seq')
                        : $db->getLastInsertID();
                foreach ($languages as $language) {
                    $messageData = [
                        'id'        => $lastID,
                        'language'  => $language,
                    ];
                    if ( true === $hasLocationColumn ) {
                        $messageData['hash'] = $msgHash;
                    }
                    $db->createCommand()
                        ->insert($messageTable, $messageData)
                        ->execute()
                    ;
                }
            }
        }

        $this->stdout($savedFlag ? "saved." . PHP_EOL : "Nothing new...skipped." . PHP_EOL);
        $this->stdout($removeUnused ? "Deleting obsoleted messages..." . PHP_EOL : "Updating obsoleted messages..." . PHP_EOL);

        if (empty($obsolete)) {
            $this->stdout("Nothing obsoleted!...skipped." . PHP_EOL);
        } else {
            if ($removeUnused) {
                $db->createCommand()
                   ->delete($sourceMessageTable, ['in', 'id', $obsolete])->execute();
                $this->stdout("deleted." . PHP_EOL);
            } else {
                $db->createCommand()
                    ->update(
                        $sourceMessageTable,
                        ['message' => new \yii\db\Expression("CONCAT('@@',message,'@@')")],
                        ['in', 'id', $obsolete]
                    )
                    ->execute()
                ;
                $this->stdout("updated." . PHP_EOL);
            }
        }

        // ------------------------------ COUNTER ------------------------------
        $counter = ['new' => 0, 'obsolete' => count($obsolete)];
        foreach ( $new as $msgs ) {
            $counter['new'] += count($msgs);
        }

        return $counter;
    }

    /**
     * @param string $category
     * @param string $message
     * @return string
     */
    protected function extractLocations($category, $message)
    {
        $output  = [];
        $msgHash = md5($message);

        foreach ( $this->locations[$category] as $location ) {
            if ( isset($location[$msgHash]) ) {
                $output[] = $location[$msgHash];
            }
        }

        return Json::encode($output);
    }

    /**
     * Extracts messages from a file
     *
     * @param string $fileName name of the file to extract messages from
     * @param string $translator name of the function used to translate messages
     * @return array
     */
    protected function extractMessages($fileName, $translator)
    {
        $coloredFileName = Console::ansiFormat($fileName, [Console::FG_CYAN]);
        $this->stdout("Extracting messages from $coloredFileName...\n");

        $subject  = file_get_contents($fileName);
        $messages = [];
        foreach ((array)$translator as $currentTranslator) {
            $translatorTokens = token_get_all('<?php ' . $currentTranslator);
            array_shift($translatorTokens);

            $translatorTokensCount = count($translatorTokens);
            $matchedTokensCount = 0;
            $buffer = [];

            $tokens = token_get_all($subject);
            foreach ($tokens as $token) {
                // finding out translator call
                if ($matchedTokensCount < $translatorTokensCount) {
                    if ($this->tokensEqual($token, $translatorTokens[$matchedTokensCount])) {
                        $matchedTokensCount++;
                    } else {
                        $matchedTokensCount = 0;
                    }
                } elseif ($matchedTokensCount === $translatorTokensCount) {
                    // translator found
                    // end of translator call or end of something that we can't extract
                    if ($this->tokensEqual(')', $token)) {
                        if (isset($buffer[0][0], $buffer[1], $buffer[2][0]) && $buffer[0][0] === T_CONSTANT_ENCAPSED_STRING && $buffer[1] === ',' && $buffer[2][0] === T_CONSTANT_ENCAPSED_STRING) {
                            // is valid call we can extract

                            $category = stripcslashes($buffer[0][1]);
                            $category = mb_substr($category, 1, mb_strlen($category) - 2);

                            $message = stripcslashes($buffer[2][1]);
                            $message = mb_substr($message, 1, mb_strlen($message) - 2);

                            $messages[$category][] = $message;
                            foreach ($this->config['sourcePath'] as $sourcePath) {
                                $location = str_replace(realpath($sourcePath), '', $fileName);
                                if ( $location !== $fileName ) {
                                    $parts = explode('/', $sourcePath);
                                    $key   = count($parts) - 1;
                                    $this->locations[$category][] = [md5($message) => $parts[$key] . $location];
                                }
                            }
                        } else {
                            // invalid call or dynamic call we can't extract
                            $line = Console::ansiFormat($this->getLine($buffer), [Console::FG_CYAN]);
                            $skipping = Console::ansiFormat('Skipping line', [Console::FG_YELLOW]);
                            $this->stdout("$skipping $line. Make sure both category and message are static strings.\n");
                        }

                        // prepare for the next match
                        $matchedTokensCount = 0;
                        $buffer = [];
                    } elseif ($token !== '(' && isset($token[0]) && !in_array($token[0], [T_WHITESPACE, T_COMMENT])) {
                        // ignore comments, whitespaces and beginning of function call
                        $buffer[] = $token;
                    }
                }
            }
        }

        return $messages;
    }

    /**
     * Finds out if two PHP tokens are equal
     *
     * @param array|string $a
     * @param array|string $b
     * @return boolean
     * @since 2.0.1
     */
    protected function tokensEqual($a, $b)
    {
        if (is_string($a) && is_string($b)) {
            return $a === $b;
        } elseif (isset($a[0], $a[1], $b[0], $b[1])) {
            return $a[0] === $b[0] && $a[1] == $b[1];
        }

        return false;
    }

    /**
     * Finds out a line of the first non-char PHP token found
     *
     * @param array $tokens
     * @return int|string
     * @since 2.0.1
     */
    protected function getLine($tokens)
    {
        foreach ($tokens as $token) {
            if (isset($token[2])) {
                return $token[2];
            }
        }

        return 'unknown';
    }

    /**
     * Writes messages into PHP files
     *
     * @param array $messages
     * @param string $dirName name of the directory to write to
     * @param boolean $overwrite if existing file should be overwritten without backup
     * @param boolean $removeUnused if obsolete translations should be removed
     * @param boolean $sort if translations should be sorted
     */
    protected function saveMessagesToPHP($messages, $dirName, $overwrite, $removeUnused, $sort)
    {
        foreach ($messages as $category => $msgs) {
            $file = str_replace("\\", '/', "$dirName/$category.php");
            $path = dirname($file);
            FileHelper::createDirectory($path);
            $msgs = array_values(array_unique($msgs));
            $coloredFileName = Console::ansiFormat($file, [Console::FG_CYAN]);
            $this->stdout("Saving messages to $coloredFileName...\n");
            $this->saveMessagesCategoryToPHP($msgs, $file, $overwrite, $removeUnused, $sort, $category);
        }
    }

    /**
     * Writes category messages into PHP file
     *
     * @param array $messages
     * @param string $fileName name of the file to write to
     * @param boolean $overwrite if existing file should be overwritten without backup
     * @param boolean $removeUnused if obsolete translations should be removed
     * @param boolean $sort if translations should be sorted
     * @param string $category message category
     */
    protected function saveMessagesCategoryToPHP($messages, $fileName, $overwrite, $removeUnused, $sort, $category)
    {
        if (is_file($fileName)) {
            $existingMessages = require($fileName);
            sort($messages);
            ksort($existingMessages);
            if (array_keys($existingMessages) == $messages) {
                return $this->stdout("Nothing new in \"$category\" category... Nothing to save.\n\n", Console::FG_GREEN);
            }
            $merged = [];
            $untranslated = [];
            foreach ($messages as $message) {
                if (array_key_exists($message, $existingMessages) && $existingMessages[$message] !== '') {
                    $merged[$message] = $existingMessages[$message];
                } else {
                    $untranslated[] = $message;
                }
            }
            ksort($merged);
            sort($untranslated);
            $todo = [];
            foreach ($untranslated as $message) {
                $todo[$message] = '';
            }
            ksort($existingMessages);
            foreach ($existingMessages as $message => $translation) {
                if (!$removeUnused && !isset($merged[$message]) && !isset($todo[$message])) {
                    if (!empty($translation) && strncmp($translation, '@@', 2) === 0 && substr_compare($translation, '@@', -2, 2) === 0) {
                        $todo[$message] = $translation;
                    } else {
                        $todo[$message] = '@@' . $translation . '@@';
                    }
                }
            }
            $merged = array_merge($todo, $merged);
            if ($sort) {
                ksort($merged);
            }
            if (false === $overwrite) {
                $fileName .= '.merged';
            }
            $this->stdout("Translation merged.\n");
        } else {
            $merged = [];
            foreach ($messages as $message) {
                $merged[$message] = '';
            }
            ksort($merged);
        }


        $array = VarDumper::export($merged);
        $content = <<<EOD
<?php
/**
 * Message translations.
 *
 * This file is automatically generated by 'yii message' command.
 * It contains the localizable messages extracted from source code.
 * You may modify this file by translating the extracted messages.
 *
 * Each array element represents the translation (value) of a message (key).
 * If the value is empty, the message is considered as not translated.
 * Messages that no longer need translation will have their translations
 * enclosed between a pair of '@@' marks.
 *
 * Message string can be used with plural forms format. Check i18n section
 * of the guide for details.
 *
 * NOTE: this file must be saved in UTF-8 encoding.
 */
return $array;

EOD;

        file_put_contents($fileName, $content);
        $this->stdout("Translation saved.\n", Console::FG_GREEN);
    }

    /**
     * Writes messages into PO file
     *
     * @param array $messages
     * @param string $dirName name of the directory to write to
     * @param boolean $overwrite if existing file should be overwritten without backup
     * @param boolean $removeUnused if obsolete translations should be removed
     * @param boolean $sort if translations should be sorted
     * @param string $catalog message catalog
     */
    protected function saveMessagesToPO($messages, $dirName, $overwrite, $removeUnused, $sort, $catalog)
    {
        $file = str_replace("\\", '/', "$dirName/$catalog.po");
        FileHelper::createDirectory(dirname($file));
        $this->stdout("Saving messages to $file...\n");

        $poFile = new GettextPoFile();

        $merged = [];
        $todos = [];

        $hasSomethingToWrite = false;
        foreach ($messages as $category => $msgs) {
            $notTranslatedYet = [];
            $msgs = array_values(array_unique($msgs));

            if (is_file($file)) {
                $existingMessages = $poFile->load($file, $category);

                sort($msgs);
                ksort($existingMessages);
                if (array_keys($existingMessages) == $msgs) {
                    $this->stdout("Nothing new in \"$category\" category...\n");

                    sort($msgs);
                    foreach ($msgs as $message) {
                        $merged[$category . chr(4) . $message] = $existingMessages[$message];
                    }
                    ksort($merged);
                    continue;
                }

                // merge existing message translations with new message translations
                foreach ($msgs as $message) {
                    if (array_key_exists($message, $existingMessages) && $existingMessages[$message] !== '') {
                        $merged[$category . chr(4) . $message] = $existingMessages[$message];
                    } else {
                        $notTranslatedYet[] = $message;
                    }
                }
                ksort($merged);
                sort($notTranslatedYet);

                // collect not yet translated messages
                foreach ($notTranslatedYet as $message) {
                    $todos[$category . chr(4) . $message] = '';
                }

                // add obsolete unused messages
                foreach ($existingMessages as $message => $translation) {
                    if (!$removeUnused && !isset($merged[$category . chr(4) . $message]) && !isset($todos[$category . chr(4) . $message])) {
                        if (!empty($translation) && substr($translation, 0, 2) === '@@' && substr($translation, -2) === '@@') {
                            $todos[$category . chr(4) . $message] = $translation;
                        } else {
                            $todos[$category . chr(4) . $message] = '@@' . $translation . '@@';
                        }
                    }
                }

                $merged = array_merge($todos, $merged);
                if ($sort) {
                    ksort($merged);
                }

                if ($overwrite === false) {
                    $file .= '.merged';
                }
            } else {
                sort($msgs);
                foreach ($msgs as $message) {
                    $merged[$category . chr(4) . $message] = '';
                }
                ksort($merged);
            }
            $this->stdout("Category \"$category\" merged.\n");
            $hasSomethingToWrite = true;
        }
        if ($hasSomethingToWrite) {
            $poFile->save($file, $merged);
            $this->stdout("Translation saved.\n", Console::FG_GREEN);
        } else {
            $this->stdout("Nothing to save.\n", Console::FG_GREEN);
        }
    }

    /**
     * Prints a string to STDOUT
     *
     * You may optionally format the string with ANSI codes by
     * passing additional parameters using the constants defined in [[\yii\helpers\Console]].
     *
     * Example:
     *
     * ~~~
     * $this->stdout('This will be red and underlined.', Console::FG_RED, Console::UNDERLINE);
     * ~~~
     *
     * @param string $string the string to print
     * @return int|boolean Number of bytes printed or false on error
     */
    public function stdout($string)
    {
        if ( Yii::$app->id != 'app-console' )
            return false;

        if ($this->isColorEnabled()) {
            $args = func_get_args();
            array_shift($args);
            $string = Console::ansiFormat($string, $args);
        }

        return Console::stdout($string);
    }

    /**
     * Returns a value indicating whether ANSI color is enabled.
     *
     * ANSI color is enabled only if [[color]] is set true or is not set
     * and the terminal supports ANSI color.
     *
     * @param resource $stream the stream to check.
     * @return boolean Whether to enable ANSI style in output.
     */
    public function isColorEnabled($stream = \STDOUT)
    {
        return $this->color === null ? Console::streamSupportsAnsiColors($stream) : $this->color;
    }

    /**
     * Returns ids of messages marked as deleted
     * @return array
     */
    public static function getDeletedIds()
    {
        return self::find()->select('id')->deleted()->column();
    }
}
