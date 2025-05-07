

DELIMITER $$
CREATE TRIGGER after_shop_payroll_update 
    AFTER UPDATE ON payrolls
    FOR EACH ROW 
BEGIN

DECLARE provider INT(11);
DECLARE wallet DOUBLE(10,2);
DECLARE bankdetail INT(11);

DECLARE admin_close_balance DOUBLE(10,2);
DECLARE provider_close_balance DOUBLE(10,2);

IF NEW.status = 'COMPLETED' THEN

	IF NEW.type = "SHOP" THEN

		SELECT `id`, `wallet_balance`, `is_bankdetail` INTO provider, wallet, bankdetail FROM `gojek_order`.`stores` WHERE id = NEW.shop_id;
		SELECT `close_balance` INTO admin_close_balance FROM ((SELECT `close_balance` FROM `admin_wallets` ORDER BY id DESC LIMIT 1) UNION (SELECT 0) ) a LIMIT 1;
		SELECT `close_balance` INTO provider_close_balance FROM ((SELECT `close_balance` FROM `gojek_order`.`store_wallets` ORDER BY id DESC LIMIT 1) UNION (SELECT 0) ) b LIMIT 1;

		IF (wallet > 0 AND bankdetail = 1 ) THEN

			INSERT INTO `gojek_common`.`admin_wallets`(`company_id`, `admin_service`, `transaction_id`, `transaction_alias`, `transaction_desc`, `transaction_type`, `type`, `wallet_type`, `amount`, `open_balance`, `close_balance`, `created_at`, `updated_at`) VALUES(NEW.company_id, NEW.admin_service, NEW.id, NEW.transaction_id, 'Amount transferred to store', 5, 'D', 'ADMIN', CONCAT('-',NEW.wallet), admin_close_balance, (admin_close_balance - NEW.wallet), NOW(), NOW());

			INSERT INTO `gojek_order`.`store_wallets`(`company_id`, `admin_service`, `store_id`, `transaction_id`, `transaction_alias`, `transaction_desc`, `type`, `amount`, `open_balance`, `close_balance`, `created_at`, `updated_at`) VALUES(NEW.company_id, NEW.admin_service, provider, NEW.id, NEW.transaction_id, 'Amount deducted by admin', 'D', CONCAT('-',NEW.wallet), provider_close_balance, (provider_close_balance - NEW.wallet ), NOW(), NOW() );

	    	UPDATE `gojek_order`.`stores` SET `wallet_balance` = (wallet - NEW.wallet) WHERE `id` = provider; 

    	ELSE

	    	IF wallet <= 0 THEN

	    		SIGNAL sqlstate '45001' set message_text = "Wallet balance is less than zero";

	    	ELSEIF bankdetail <> 1 THEN

	    		SIGNAL sqlstate '45001' set message_text = "Bankdetail not added";

	    	END IF;

    	END IF;

	END IF;

END IF;

END$$
DELIMITER ;



