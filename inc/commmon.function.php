<?php


/*
 ----------------------------------------------------------------------
 GLPI - Gestionnaire Libre de Parc Informatique
 Copyright (C) 2003-2008 by the INDEPNET Development Team.

 http://indepnet.net/   http://glpi-project.org/
 ----------------------------------------------------------------------

 LICENSE

	This file is part of GLPI.

    GLPI is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    GLPI is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with GLPI; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 ------------------------------------------------------------------------
*/

// Original Author of file: Walid Nouh
// Purpose of file:
// ----------------------------------------------------------------------

/**
 * Get the object class name, by giving the name
 * @param name the object's internal name
 * @return the classname associated with the object
 */
function plugin_genericobject_getObjectTypeByName($name) {
	return $classname = 'PluginGenericobject' . ucfirst($name);
}

/**
 * Get the object table name, by giving the identifier
 * @param name the object's internal identifier
 * @return the classname associated with the object
 */
function plugin_genericobject_getObjectTableNameByName($name) {
	return 'glpi_plugin_genericobject_' . $name."s";
}

/**
 * Get the object ID, by giving the name
 * @param name the object's internal identifier
 * @return the ID associated with the object
 */
function plugin_genericobject_getObjectIdentifierByName($name) {
	return 'PLUGIN_GENERICOBJECT_' . strtoupper($name) . '_TYPE';
}

/**
 * Get the object class, by giving the name
 * @param name the object's internal identifier
 * @return the class associated with the object
 */
function plugin_genericobject_getObjectClassByName($name) {
	return 'PluginGenericobject' . ucfirst($name);
}

/**
 * Get all types of active&published objects
 */
function plugin_genericobject_getAllTypes($all = false) {
	if (TableExists("glpi_plugin_genericobject_types")) {
		if (!$all)
			$where = " status=" . GENERICOBJECT_OBJECTTYPE_STATUS_ACTIVE;
		else
			$where = '';
		return getAllDatasFromTable("glpi_plugin_genericobject_types", $where);
	} else
		return array ();
}

/**
 * Get an internal ID by the object name
 * @param name the object's name
 * @return the object's ID
 */
function plugin_genericobject_getIDByName($name) {
	global $DB;
	$query = "SELECT itemtype FROM `glpi_plugin_genericobject_types` WHERE name='$name'";
	$result = $DB->query($query);
	if ($DB->numrows($result))
		return $DB->result($result, 0, "itemtype");
	else
		return 0;
}

/**
 * Get object name by ID
 * @param ID the internal ID
 * @return the name associated with the ID
 */
function plugin_genericobject_getNameByID($itemtype) {
	global $DB;
	$query = "SELECT name FROM `glpi_plugin_genericobject_types` WHERE itemtype='$itemtype'";
	$result = $DB->query($query);
	if ($DB->numrows($result))
		return $DB->result($result, 0, "name");
	else
		return "";
}

/**
 * Get table name by ID
 * @param ID the object's ID
 * @return the table
 */
function plugin_genericobject_getTableNameByID($ID) {
	return plugin_genericobject_getTableNameByName(plugin_genericobject_getNameByID($ID));
}

/**
 * Get table name by name
 * @param ID the object's ID
 * @return the table
 */
function plugin_genericobject_getTableNameByName($name) {
	return 'glpi_plugin_genericobject_' . $name."s";
}

/**
 * Register all object's types and values
 * @return nothing
 */
function plugin_genericobject_registerNewTypes() {
	//Only look for published and active types

	foreach (plugin_genericobject_getAllTypes() as $ID => $type)
		plugin_genericobject_registerOneType($type);
}

/**
 * Register all variables for a type
 * @param type the type's attributes
 * @return nothing
 */
function plugin_genericobject_registerOneType($type) {
	global $LANG, $DB, $PLUGIN_HOOKS, $CFG_GLPI, 
			$GENERICOBJECT_LINK_TYPES, 
			$IMPORT_PRIMARY_TYPES, $IMPORT_TYPES, $ORDER_AVAILABLE_TYPES,
			$ORDER_TYPE_TABLES,$ORDER_MODEL_TABLES, $ORDER_TEMPLATE_TABLES,
         $UNINSTALL_TYPES,$GENERICOBJECT_PDF_TYPES,$GENINVENTORYNUMBER_INVENTORY_TYPES;
	$name = $type["name"];
	$typeID = $type["itemtype"];

	$tablename = plugin_genericobject_getObjectTableNameByName($name);
	//If table doesn't exists, do not try to register !
	if (TableExists($tablename) && !defined($typeID)) {
			
		$object_identifier = plugin_genericobject_getObjectIdentifierByName($name);

		$db_fields = $DB->list_fields($tablename);
		//Include locales, 
		plugin_genericobject_includeLocales($name);
		plugin_genericobject_includeClass($name);

		/*registerPluginType('genericobject', $object_identifier, $typeID, array (
			'classname' => plugin_genericobject_getObjectClassByName($name),
			'tablename' => $tablename,
			'formpage' => 'front/plugin_genericobject.object.form.php',
			'searchpage' => 'front/plugin_genericobject.search.php',
			'typename' => (isset ($LANG["genericobject"][$name][1]) ? $LANG["genericobject"][$name][1] : $name),
			'deleted_tables' => ($type["use_deleted"] ? true : false),
			'template_tables' => ($type["use_template"] ? true : false),
			'specif_entities_tables' => ($type["use_entity"] ? true : false),
			'reservation_types' => ($type["use_loans"] ? true : false),
			'recursive_type' => ($type["use_recursivity"] ? true : false),
			'infocom_types' => ($type["use_infocoms"] ? true : false),
			'linkuser_types' => (($type["use_tickets"] && isset ($db_fields["users_id"])) ? true : false),
			'linkgroup_types' => (($type["use_tickets"] && isset ($db_fields["groups_id"])) ? true : false),
			
		));*/
      array_push($GENERICOBJECT_LINK_TYPES, $typeID);
      
      if ($type['use_network_ports']) {
      	array_push($CFG_GLPI["netport_types"],$typeID);
      }
      //If helpdesk functionnality is on, and helpdesk_visible field exists for this object type
      if ($type['use_tickets'] && isset($db_fields['helpdesk_visible'])) {
         array_push($CFG_GLPI['helpdesk_visible_types'],$typeID);
      }
      
      $plugin = new Plugin;

		//Integration with datainjection plugin
      if ($type["use_plugin_datainjection"] && $plugin->isActivated("datainjection")) {
          //usePlugin("datainjection");
         Plugin::load("datainjection");
         $PLUGIN_HOOKS['datainjection'][$name] = "plugin_genericobject_datainjection_variables";
			$IMPORT_PRIMARY_TYPES[] = $typeID;
			$IMPORT_TYPES[] = $typeID;
		}
		//End integration with datainjection plugin

      //Integration with geninventorynumber plugin
      if ($type["use_plugin_geninventorynumber"] && $plugin->isActivated("geninventorynumber")) {
          //usePlugin("geninventorynumber");
         Plugin::load("geninventorynumber");
         $infos = plugin_version_geninventorynumber();
         if ($infos['version'] >= '1.3.0') {
            array_push($GENINVENTORYNUMBER_INVENTORY_TYPES, $typeID);	
         }
         
      }
      //End integration with geninventorynumber plugin


      //Integration with order management plugin
		if ($type["use_plugin_order"] && $plugin->isActivated("order")) {
			//usePlugin("order");
			Plugin::load("order");
			$ORDER_AVAILABLE_TYPES[] = $typeID;
			if (isset ($db_fields["type"]))
				$ORDER_TYPE_TABLES[$typeID] = plugin_genericobject_getDropdownTableName($name,'type');
			if (isset ($db_fields["model"]))
				$ORDER_MODEL_TABLES[$typeID] = plugin_genericobject_getDropdownTableName($name,'model');
			if ($type["use_template"])
				$ORDER_TEMPLATE_TABLES[] = $typeID;
		}
		//End integration with order plugin
		
		if ($type["use_template"]) {
			$PLUGIN_HOOKS['submenu_entry']['genericobject']['template'][$name] = 'front/template.php?itemtype=' . $typeID . '&amp;add=0';
			$PLUGIN_HOOKS['submenu_entry']['genericobject']['add'][$name] = 'front/template.php?itemtype=' . $typeID . '&amp;add=1';
		} else
			$PLUGIN_HOOKS['submenu_entry']['genericobject']['add'][$name] = 'front/object.form.php?itemtype=' . $typeID;

		$PLUGIN_HOOKS['submenu_entry']['genericobject']['search'][$name] = 'front/search.php?itemtype=' . $typeID;

        if ($type['use_plugin_uninstall'] && $plugin->isActivated('uninstall')) {
           Plugin::load("uninstall");
           $UNINSTALL_TYPES[] = $typeID;
        }

		// Later, when per entity and tree dropdowns will be managed !
		foreach (plugin_genericobject_getSpecificDropdownsTablesByType($typeID) as $table => $name) {
			array_push($CFG_GLPI["specif_entities_tables"], $table);
			//array_push($CFG_GLPI["dropdowntree_tables"], $table);
			
			//$PLUGIN_HOOKS['submenu_entry']['genericobject']['add'][$name.$field] = "front/$name.$field.php";
		}

	}
}

/**
 * Add search options for an object type
 * @param name the internal object name
 * @return an array with all search options
 */
function plugin_genericobject_objectSearchOptions($name, $search_options = array ()) {
	global $DB, $GENERICOBJECT_AVAILABLE_FIELDS, $LANG;

	$table = plugin_genericobject_getObjectTableNameByName($name);

	if (TableExists($table)) {
		$type = plugin_genericobject_getObjectIdentifierByName($name);
		$ID = plugin_genericobject_getIDByName($name);
		$fields = $DB->list_fields($table);
		$i = 1;

		$search_options[80]['table'] = 'glpi_entities';
		$search_options[80]['field'] = 'completename';
		$search_options[80]['linkfield'] = 'entities_id';
		$search_options[80]['name'] = $LANG["entity"][0];

      $search_options[30]['table'] = $table;
      $search_options[30]['field'] = 'ID';
      $search_options[30]['linkfield'] = '';
      $search_options[30]['name'] = $LANG["common"][2];

		if (!empty ($fields)) {
			$search_options['common'] = plugin_genericobject_getObjectLabel($name);
			foreach ($fields as $field_values) {
				$field_name = $field_values['Field'];
				if (isset ($GENERICOBJECT_AVAILABLE_FIELDS[$field_name])) {
					$search_options[$i]['linkfield'] = '';

					switch ($GENERICOBJECT_AVAILABLE_FIELDS[$field_name]['input_type']) {
						case 'date' :
						case 'text' :
                  case 'multitext' :
							$search_options[$i]['table'] = plugin_genericobject_getObjectTableNameByName($name);
							break;
						case 'dropdown' :
							if (plugin_genericobject_isDropdownTypeSpecific($field_name))
								$search_options[$i]['table'] = plugin_genericobject_getDropdownTableName($name, $field_name);
							else
								$search_options[$i]['table'] = $GENERICOBJECT_AVAILABLE_FIELDS[$field_name]['table'];

							$search_options[$i]['linkfield'] = $GENERICOBJECT_AVAILABLE_FIELDS[$field_name]['linkfield'];
							break;
						case 'dropdown_yesno' :
                  case 'dropdown_global' :
							$search_options[$i]['table'] = plugin_genericobject_getObjectTableNameByName($name);
							$search_options[$i]['linkfield'] = $GENERICOBJECT_AVAILABLE_FIELDS[$field_name]['linkfield'];
							break;
					}
               
					$search_options[$i]['field'] = $GENERICOBJECT_AVAILABLE_FIELDS[$field_name]['field'];
					$search_options[$i]['name'] = $GENERICOBJECT_AVAILABLE_FIELDS[$field_name]['name'];
					if (isset ($GENERICOBJECT_AVAILABLE_FIELDS[$field_name]['datatype']))
						$search_options[$i]['datatype'] = $GENERICOBJECT_AVAILABLE_FIELDS[$field_name]['datatype'];

					$i++;
				}

			}
		}

	}
	return $search_options;
}

/**
 * Get an object type configuration by itemtype
 * @param itemtype the object device type
 * @return an array which contains all the type's configuration
 */
function plugin_genericobject_getObjectTypeConfiguration($itemtype) {
	$objecttype = new PluginGenericObjectType;
	$objecttype->getFromDBByType($itemtype);
	return $objecttype->fields;
}

function plugin_genericobject_addObjectTypeDirectory($name) {

}
/**
 * Include locales for a specific type
 * @name object type's name
 * @return nothing
 */
function plugin_genericobject_includeLocales($name) {
	global $CFG_GLPI, $LANG;

	$prefix = GLPI_ROOT . "/plugins/genericobject/objects/" . $name . "/" . $name;
	if (isset ($_SESSION["glpilanguage"]) && file_exists($prefix . "." . $CFG_GLPI["languages"][$_SESSION["glpilanguage"]][1])) {
		include_once ($prefix . "." . $CFG_GLPI["languages"][$_SESSION["glpilanguage"]][1]);

	} else
		if (file_exists($prefix . ".en_GB.php")) {
			include_once ($prefix . ".en_GB.php");

		} else
			if (file_exists($prefix . ".fr_FR.php")) {
				include_once ($prefix . ".fr_FR.php");

			} else {
				return false;
			}
	return true;
}

/**
 * Include object type class
 * @name object type's name
 * @return nothing
 */
function plugin_genericobject_includeClass($name) {
	//If class comes directly with the plugin
	if (file_exists(GLPI_ROOT . "/plugins/genericobject/objects/$name/$name.class.php")) {
		include_once (GLPI_ROOT . "/plugins/genericobject/objects/$name/$name.class.php");
	} else {
		include_once (GENERICOBJECT_CLASS_PATH . '/' . $name . '.class.php');
	}

}

function plugin_genericobject_getObjectLabel($name) {
	global $LANG;
	if (isset ($LANG['genericobject'][$name][1]))
		return $LANG['genericobject'][$name][1];
	else
		return $name;
}

?>
