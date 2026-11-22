<?php
// includes/db.php - JSON Database Engine with File Locking & Atomic Operations

require_once __DIR__ . '/config.php';

/**
 * Resolves absolute path to a JSON table
 */
function json_db_path($table) {
    // Sanitize table name against path traversal
    $clean_table = preg_replace('/[^a-zA-Z0-9_\-]/', '', $table);
    return DATA_DIR . DIRECTORY_SEPARATOR . $clean_table . '.json';
}

/**
 * Reads data from JSON file with shared file locking (LOCK_SH)
 */
function json_db_read($table) {
    $path = json_db_path($table);
    if (!file_exists($path)) {
        return [];
    }

    $fp = fopen($path, 'r');
    if (!$fp) return [];

    flock($fp, LOCK_SH);
    $content = stream_get_contents($fp);
    flock($fp, LOCK_UN);
    fclose($fp);

    if (empty($content)) {
        return [];
    }

    $data = json_decode($content, true);
    return is_array($data) ? $data : [];
}

/**
 * Writes data to JSON file with exclusive file locking (LOCK_EX)
 */
function json_db_write($table, array $data) {
    $path = json_db_path($table);
    $json = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    
    $fp = fopen($path, 'c+');
    if (!$fp) return false;

    if (flock($fp, LOCK_EX)) {
        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, $json);
        fflush($fp);
        flock($fp, LOCK_UN);
        fclose($fp);
        return true;
    }

    fclose($fp);
    return false;
}

/**
 * Find all records matching optional callback filter
 */
function json_db_find_all($table, callable $filter = null) {
    $rows = json_db_read($table);
    if ($filter === null) {
        return $rows;
    }
    return array_values(array_filter($rows, $filter));
}

/**
 * Find single record by integer ID
 */
function json_db_find_by_id($table, $id) {
    $id = (int)$id;
    $rows = json_db_read($table);
    foreach ($rows as $row) {
        if (isset($row['id']) && (int)$row['id'] === $id) {
            return $row;
        }
    }
    return null;
}

/**
 * Find first record matching callback
 */
function json_db_find_one($table, callable $callback) {
    $rows = json_db_read($table);
    foreach ($rows as $row) {
        if ($callback($row)) {
            return $row;
        }
    }
    return null;
}

/**
 * Insert new record with auto-increment ID
 */
function json_db_insert($table, array $record) {
    $path = json_db_path($table);
    $fp = fopen($path, 'c+');
    if (!$fp) return false;

    if (flock($fp, LOCK_EX)) {
        $content = stream_get_contents($fp);
        $rows = !empty($content) ? json_decode($content, true) : [];
        if (!is_array($rows)) $rows = [];

        // Auto-increment ID
        $max_id = 0;
        foreach ($rows as $r) {
            if (isset($r['id']) && $r['id'] > $max_id) {
                $max_id = (int)$r['id'];
            }
        }
        $record['id'] = $max_id + 1;
        if (!isset($record['created_at'])) {
            $record['created_at'] = date('Y-m-d H:i:s');
        }

        $rows[] = $record;

        $json = json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        ftruncate($fp, 0);
        rewind($fp);
        fwrite($fp, $json);
        fflush($fp);
        flock($fp, LOCK_UN);
        fclose($fp);
        return $record;
    }

    fclose($fp);
    return false;
}

/**
 * Update record by ID
 */
function json_db_update($table, $id, array $updates) {
    $id = (int)$id;
    $path = json_db_path($table);
    $fp = fopen($path, 'c+');
    if (!$fp) return false;

    $updated_record = null;

    if (flock($fp, LOCK_EX)) {
        $content = stream_get_contents($fp);
        $rows = !empty($content) ? json_decode($content, true) : [];
        if (!is_array($rows)) $rows = [];

        foreach ($rows as &$r) {
            if (isset($r['id']) && (int)$r['id'] === $id) {
                unset($updates['id']); // Do not overwrite ID
                $r = array_merge($r, $updates);
                $r['updated_at'] = date('Y-m-d H:i:s');
                $updated_record = $r;
                break;
            }
        }

        if ($updated_record) {
            $json = json_encode($rows, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            ftruncate($fp, 0);
            rewind($fp);
            fwrite($fp, $json);
            fflush($fp);
        }

        flock($fp, LOCK_UN);
        fclose($fp);
        return $updated_record;
    }

    fclose($fp);
    return false;
}

/**
 * Delete record by ID
 */
function json_db_delete($table, $id) {
    $id = (int)$id;
    $path = json_db_path($table);
    $fp = fopen($path, 'c+');
    if (!$fp) return false;

    $deleted = false;

    if (flock($fp, LOCK_EX)) {
        $content = stream_get_contents($fp);
        $rows = !empty($content) ? json_decode($content, true) : [];
        if (!is_array($rows)) $rows = [];

        $new_rows = [];
        foreach ($rows as $r) {
            if (isset($r['id']) && (int)$r['id'] === $id) {
                $deleted = true;
                continue;
            }
            $new_rows[] = $r;
        }

        if ($deleted) {
            $json = json_encode(array_values($new_rows), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
            ftruncate($fp, 0);
            rewind($fp);
            fwrite($fp, $json);
            fflush($fp);
        }

        flock($fp, LOCK_UN);
        fclose($fp);
        return $deleted;
    }

    fclose($fp);
    return false;
}
