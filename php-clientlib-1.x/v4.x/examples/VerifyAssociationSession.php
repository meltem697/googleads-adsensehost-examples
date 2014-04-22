<?php
/*
 * Copyright 2014 Google Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * This example verifies an association session.
 *
 * Tags: associationsessions.verify
 */
class VerifyAssociationSession {
  /**
   * Verifies an association session.
   *
   * @param $service Google_Service_AdSenseHost AdSenseHost service object on
   *     which to run the requests.
   * @param $callbackToken string AdSenseHost the token returned in the
   *     association callback.
   */
  public static function run($service, $callbackToken) {
    $separator = str_repeat('=', 80) . "\n";
    print $separator;
    print "Creating new association session\n";
    print $separator;

    $result = $service->associationsessions->verify($callbackToken);

    printf("Association for account \"%s\" has status \"%s\" and ID \"%s\".\n",
        $result->getAccountId(), $result->getStatus(), $result->getId());

    print "\n";

    return $result;
  }
}
