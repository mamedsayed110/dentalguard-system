<?php
namespace App\Http\Middleware;

use Closure;

class CheckRole {
   public function handle($request, Closure $next, ...$roles){
      $user = auth()->user();
      if(!$user || !in_array($user->role->value,$roles)) abort(403);
      return $next($request);
   }
}
