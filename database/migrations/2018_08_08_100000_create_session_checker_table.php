<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSessionCheckerTable extends Migration
{
    /**
     * The database schema.
     *
     * @var \Illuminate\Database\Schema\Builder
     */
    protected $schema;

    /**
     * Create a new migration instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->schema = Schema::connection($this->getConnection());
    }

    /**
     * Get the migration connection name.
     *
     * @return string|null
     */
    public function getConnection()
    {
        return config('telescope.storage.database.connection');
    }

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $this->schema->create('session_checker', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('SERVER_ADDR')->nullable();
            $table->string('HTTP_HOST')->nullable();
            $table->string('HTTP_USER_AGENT')->nullable();
            $table->longText('session_data');
            $table->dateTime('created_at')->nullable();
            $table->dateTime('handled_at')->nullable();

            $table->index('created_at');
            $table->index('HTTP_HOST');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        $this->schema->dropIfExists('session_checker');
    }
}
