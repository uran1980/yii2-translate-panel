<?php

namespace uran1980\yii\modules\i18n\components;

use \yii\i18n\DbMessageSource;

class I18N extends \Zelenin\yii\modules\I18n\components\I18N
{
    /**
     * String, required, root directory of all source files
     * for example: __DIR__ . '/..'
     *
     * @var string
     */
    public $sourcePath = '';

    /**
     * String, the name of the function for translating messages.
     * Defaults to 'Yii::t'. This is used as a mark to find the messages to be
     * translated. You may use a string for single function name or an array for
     * multiple function names.
     *
     * @var string
     */
    public $translator = 'Yii::t';

    /**
     * boolean, whether to force translation or not.
     * Defaults to false.In that case, the source message will be displayed as in
     * the code instead of what you could have set in database. When set to true,
     * the version in database will be used instead of the code one, and you will
     * be able to edit the messages for the sourceLanguage locale
     *
     * @var boolean
     */
    public $forceTranslation = false;

    /**
     * boolean, whether to sort messages by keys when merging new messages
     * with the existing ones. Defaults to false, which means the new (untranslated)
     * messages will be separated from the old (translated) ones.
     *
     * @var boolean
     */
    public $sort = false;

    /**
     * boolean, whether to remove messages that no longer appear in the source code.
     * Defaults to false, which means each of these messages will be enclosed with
     * a pair of '@@' marks.
     *
     * @var boolean
     */
    public $removeUnused = false;

    /**
     * Array, list of patterns that specify which files/directories should NOT be processed.
     * If empty or not set, all files/directories will be processed.
     *
     * A path matches a pattern if it contains the pattern string at its end. For example,
     * '/a/b' will match all files and directories ending with '/a/b';
     * the '*.svn' will match all files and directories whose name ends with '.svn'.
     * and the '.svn' will match all files and directories named exactly '.svn'.
     * Note, the '/' characters in a pattern matches both '/' and '\'.
     *
     * See helpers/FileHelper::findFiles() description for more details on pattern matching rules.
     *
     * @var array
     */
    public $only = ['*.php'];

    /**
     * Array, list of patterns that specify which files (not directories) should be processed.
     * If empty or not set, all files will be processed.
     *
     * Please refer to "except" for details about the patterns.
     * If a file/directory matches both a pattern in "only" and "except", it will NOT be processed.
     *
     * @var array
     */
    public $except = [
        '.svn',
        '.git',
        '.gitignore',
        '.gitkeep',
        '.hgignore',
        '.hgkeep',
        '/messages',
    ];

    /**
     * Output format:
     *   'php' - output format is for saving messages to php files.
     *   'db'  - output format is for saving messages to database.
     *   'po'  - output format is for saving messages to gettext po files.
     */
    public $format = 'php';

    /**
     * Connection component to use. Optional
     * (used only for 'db' format).
     *
     * @var string
     */
    public $db = 'db';

    /**
     * Root directory containing message translations
     * for example: __DIR__  . '/../../messages'
     * (used only for 'php' or 'po' formats).
     *
     * @var string
     */
    public $messagePath = '';

    /**
     * Boolean, whether the message file should be overwritten with the merged messages
     * (used only for 'php' or 'po' formats).
     *
     * @var boolean
     */
    public $overwrite = true;

    /**
     * Name of the file that will be used for translations
     * (used only for 'po' format).
     *
     * @var string
     */
    public $catalog = 'messages';

    /**
     * @throws InvalidConfigException
     */
    public function init()
    {
        if (!$this->languages) {
            throw new InvalidConfigException('You should configure i18n component [language]');
        }

        if (is_callable($this->languages)) {
            $this->languages = call_user_func($this->languages);
        }
        if (!is_array($this->languages)) {
            throw new InvalidConfigException('i18n component [language] must be an array or a callable returning an array');
        }
        if (!is_bool($this->forceTranslation)) {
            throw new InvalidConfigException('i18n component [forceTranslation] must be a boolean');
        }
        if (!empty($this->sourceLanguage) && !is_string($this->sourceLanguage)) {
            throw new InvalidConfigException('i18n component [sourceLanguage] must be a non-empty string');
        }

        $translationDefaultParams = [
            'class' => DbMessageSource::className(),
            'sourceMessageTable' => $this->sourceMessageTable,
            'messageTable' => $this->messageTable,
            'on missingTranslation' => $this->missingTranslationHandler
        ];
        if ($this->forceTranslation) {
            $translationDefaultParams['forceTranslation'] = true;
        }

        if (!isset($this->translations['*'])) {
            $this->translations['*'] = $translationDefaultParams;
        }
        if (!isset($this->translations['app']) && !isset($this->translations['app*'])) {
            $this->translations['app'] = $translationDefaultParams;
        }

        parent::init();
    }
}
