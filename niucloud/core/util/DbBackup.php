<?php
namespace core\util;

use think\facade\Cache;
use think\facade\Db;

class DbBackup
{
    private $key;
    private $backupPath;
    private $maxFileSize;
    private $currentFileIndex = 1;
    private $currentFile;
    private $currentFileSize = 0;
    private $tableOffset = [];
    private $tables = [];
    private $excludeTables = [];
    private $totalTables;
    private $processedTables = 0;
    private $restoreIndex = 0;

    private $startime = 0;

    private $maxExecuteTime = 10;

    public function __construct($backupPath, $maxFileSize = 1024 * 1024, $excludeTables = [], $key = '')
    {
        $this->key = $key;
        $cache = $this->getCache();
        $this->backupPath = $backupPath;
        $this->maxFileSize = $maxFileSize;
        $this->excludeTables = $excludeTables;
        if (!is_dir($this->backupPath)) {
            mkdir($this->backupPath, 0777, true);
        }
        $this->currentFileIndex = $cache['currentFileIndex'] ?? 0;
        $this->currentFile = $this->backupPath . '/backup_' . $this->currentFileIndex . '.sql';
        $this->tables = $cache['tables'] ?? [];
        $this->totalTables = count($this->getAllTables());
        $this->processedTables = $cache['processedTables'] ?? 0;
        $this->tableOffset = $cache['tableOffset'] ?? [];
        $this->currentFileSize = $cache['currentFileSize'] ?? 0;
        $this->restoreIndex = $cache['restoreIndex'] ?? 0;
        $this->startime = time();
    }

    private function getCache() {
        $cache = Cache::get('db_backup_' . $this->key, []);
        return $cache;
    }

    private function setCache() {
        $cache = [
            'currentFileIndex' => $this->currentFileIndex,
            'processedTables' => $this->processedTables,
            'tableOffset' => $this->tableOffset,
            'currentFileSize' => $this->currentFileSize,
            'restoreIndex' => $this->restoreIndex,
            'tables' => $this->tables
        ];
        Cache::set('db_backup_' . $this->key, $cache, 3600);
    }

    public function setExcludeTables($tables) {
        $this->tables = [];
        $this->excludeTables = $tables;
        $this->totalTables = count($this->getAllTables());
        return $this;
    }

    public function backupDatabaseSegment($limit = 1000)
    {
        $tables = array_slice($this->getAllTables(), $this->processedTables);
        if (!empty($tables)) {
            foreach ($tables as $table) {
                if (!isset($this->tableOffset[$table])) {
                    $this->tableOffset[$table] = 0;
                    $this->backupTableStructure($table);
                }

                while (true) {
                    $data = Db::table($table)->limit($this->tableOffset[$table], $limit)->select()->toArray();
                    if (empty($data)) {
                        break;
                    }
                    $this->backupTableData($table, $data);
                    $this->tableOffset[$table] += $limit;

                    if (time() - $this->startime > $this->maxExecuteTime) {
                        $this->setCache();
                        return $this->processedTables;
                    }
                }

                if (time() - $this->startime > $this->maxExecuteTime) {
                    $this->setCache();
                    return $this->processedTables;
                }

                $this->processedTables++;
            }
        }
        return true;
    }

    public function getBackupProgress() {
        return round($this->processedTables / $this->totalTables * 100);
    }

    private function backupTableStructure($table)
    {
        $dropTableQuery = "DROP TABLE IF EXISTS `$table`;\n";
        $dropLength = strlen($dropTableQuery);
        if ($this->currentFileSize + $dropLength > $this->maxFileSize) {
            $this->startNewFile();
        }
        $fp = fopen($this->currentFile, 'a');
        fwrite($fp, $dropTableQuery);
        $this->currentFileSize += $dropLength;

        $createTableQuery = $this->getTableCreateQuery($table);
        $query = $createTableQuery . ";\n";
        $queryLength = strlen($query);
        if ($this->currentFileSize + $queryLength > $this->maxFileSize) {
            $this->startNewFile();
            $fp = fopen($this->currentFile, 'a');
        }
        fwrite($fp, $query);
        fclose($fp);
        $this->currentFileSize += $queryLength;
    }

    private function backupTableData($table, $data)
    {
        $columns = implode(', ', array_map(function ($column){
            return "`{$column}`";
        }, array_keys($data[0])));
        $valueSets = [];
        foreach ($data as $row) {
            $values = [];
            foreach ($row as $value) {
                if (is_int($value)) {
                    $values[] = (string)$value; // 整数类型直接转换为字符串
                } elseif (is_float($value)) {
                    $values[] = (string)$value; // 浮点数类型直接转换为字符串
                } elseif (is_bool($value)) {
                    $values[] = $value ? '1' : '0'; // 布尔类型转换为 1 或 0
                } elseif (is_null($value)) {
                    $values[] = 'NULL'; // 空值使用 NULL
                } else {
                    $values[] = Db::getPdo()->quote($value); // 其他类型使用 quote 方法处理
                }
            }
            $valueSets[] = '(' . implode(', ', $values) . ')';
        }
        $insertQuery = "INSERT INTO `$table` ($columns) VALUES\n" . implode(",\n", $valueSets) . ";\n";
        $queryLength = strlen($insertQuery);
        if ($this->currentFileSize + $queryLength > $this->maxFileSize) {
            $this->startNewFile();
        }
        $fp = fopen($this->currentFile, 'a');
        fwrite($fp, $insertQuery);
        fclose($fp);
        $this->currentFileSize += $queryLength;
    }

    private function getAllTables()
    {
        if (!empty($this->tables)) return $this->tables;

        $tables = Db::query('SHOW TABLES');
        $this->tables = [];
        foreach ($tables as $table) {
            $table_name = current($table);
            if (in_array($table_name, $this->excludeTables)) {
                continue;
            }
            $this->tables[] = $table_name;
        }
        return $this->tables;
    }

    private function getTableCreateQuery($tableName)
    {
        $result = Db::query("SHOW CREATE TABLE $tableName");
        return $result[0]['Create Table'];
    }

    private function startNewFile()
    {
        $this->currentFileIndex++;
        $this->currentFile = $this->backupPath . '/backup_' . $this->currentFileIndex . '.sql';
        $this->currentFileSize = 0;
    }

    public function restoreDatabase()
    {
        $backupFiles = glob($this->backupPath . '/backup_*.sql');
        natsort($backupFiles);
        $backupFiles = array_values($backupFiles);

        if ($this->restoreIndex >= count($backupFiles)) return true;
        $backupFile = $backupFiles[$this->restoreIndex];

        $sql = file_get_contents($backupFile);
        $queries = explode(";\n", $sql);
        foreach ($queries as $query) {
            if (trim($query) !== '') {
                Db::execute($query);
            }
        }
        $this->restoreIndex++;
        $this->setCache();
        return $this->restoreIndex;
    }

    public function getRestoreProgress() {
        $backupFiles = glob($this->backupPath . '/backup_*.sql');
        return round($this->restoreIndex / count($backupFiles) * 100);
    }
}
