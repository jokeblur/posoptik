$table->unsignedBigInteger('branch_id')->nullable();
$table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade'); 