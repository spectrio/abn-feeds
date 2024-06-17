feeds
=====

ABN Feeds Server Code for the Scala Network



## NewRelic Installation
- You want to follow the installation Guide from when you log into newrelic.
- Add Data -> Guided Install -> Application Management (APM) -> PHP -> Follow remaining steps
- Copy the newrelic license and user api keys as they will be used in the app

If having trouble with testing the connection from newrelic with the agent, make sure you follow the steps below:

1. Verify License Key: Ensure that you have a valid New Relic license key.
2. Locate License Key: Once you have an account, navigate to the API keys page in the New Relic UI.
   If you need to create a new key, click Create a key and select the ingest-license key type.
   If you already have an existing license key, click next to the key and then click Copy key to copy it to your clipboard.
3. Configure PHP Agent: Insert the copied license key into your PHP agent's configuration file (newrelic.ini). The key should be set for the `newrelic.license` directive.
    - This is normally in the php version you have /etc/php/7.4/cli/conf.d/20-newrelic.ini
4. Restart Services: After updating the configuration file with the correct license key, restart your web server and PHP processes to apply the changes.
    - Restart nginx: sudo service nginx restart
    - Restart the EC2 instance via AWS UI Console. Feeds
      Check for Errors: If you continue to experience issues, check the PHP agent logs for any error messages that can provide more insight into the problem.
      Documentation: For more detailed instructions on license keys and PHP agent configuration, refer to the New Relic API keys documentation.

## New Relic Notifications
- The notifications for this can be found in newrelic.
- https://one.newrelic.com/alerts-ai/condition-builder/policy-entity/MjgzOTkxN3xBSU9QU3xQT0xJQ1l8NTQwMDM1MQ?account=2839917&duration=259200000&state=a71d0a07-00f0-f634-75b2-ab31097acd8f
- Check the workflow for Ring Central.

## Change PHP version On the Server
Please use to below command
`sudo update-alternatives --config php`

After run above command select the PHP version that you need to use.

Press to keep the current choice[*], or type selection number: For example 2

After switching below command used to restart the PHP and Nginx server.
`sudo service nginx restart`
sudo service nginx restart

# Getting Data from the Feed server
You have to  use mysqldump
- `mysqldump -u [user name] –p [password] [options] [database_name] [tablename] > [dumpfilename.sql]`
  For example: mysqldump -u root –p aggregator > sql_0617.sql

For Aggregator App information, you can find local setup in there.

