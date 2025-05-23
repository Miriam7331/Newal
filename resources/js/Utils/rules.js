export default {
  /**
   * Rule validate required
   * @param {any} v the given value to validate
   * @param {string} label
   * @returns validate
   */
  ruleRequired: (v, label = null) => {
    if (Array.isArray(v)) {
      return v.length !== 0 || `El ${label ?? "campo"} es obligatorio`
    }

    if (Number.isInteger(v)) {
      v = v.toString()
    }

    return !!v || `El ${label ?? "campo"} es obligatorio`
  },
  /**
   * Rule validate min length
   * @param {any} v the given value to validate
   * @param {number} length min length to check
   * @param {string} label
   * @returns validate
   */
  ruleMinLength: (v, length, label = null) => {
    return (
      String(v).length >= length ||
      `El ${label ?? "campo"} debe tener mínimo ${length} caracteres`
    )
  },
  /**
   * Rule validate max length
   * @param {any} v the given value to validate
   * @param {number} length max length to check
   * @param {string} label
   * @returns validate
   */
  ruleMaxLength: (v, length, label = null) => {
    return (
      String(v).length <= length ||
      `El ${label ?? "campo"} debe tener máximo ${length} caracteres`
    )
  },
  /**
   * Rule validate email
   * @param {any} v the given value to validate
   * @param {string} label
   * @returns validate
   */
  ruleEmail: (v, label = null) => {
    return (
      !v ||
      /^[a-zA-Z0-9-_.]+@[a-zA-Z0-9-_.]+(\.\w{2,3})+$/.test(String(v)) ||
      `El ${label ?? "campo"} debe ser un email válido`
    )
  },
  /**
   * Rule validate DNI
   * @param {any} v the given value to validate
   * @param {string} label
   * @returns validate
   */
  ruleDNI: (v, label = null) => {
    return (
      !v ||
      /^([0-9]{8}[A-Z])|[XYZ][0-9]{7}[A-Z]$/.test(String(v)) ||
      `El ${label ?? "campo"} debe ser un DNI o NIE válido`
    )
  },
  /**
   * Rule validate CIF
   * @param {any} v the given value to validate
   * @param {string} label
   * @returns validate
   */
  ruleCIF: (v, label = null) => {
    return (
      !v ||
      /^[0-9]{8}[A-Z]$|^[A-Z][0-9]{8}$/.test(String(v)) ||
      `El ${label ?? "campo"} debe ser un CIF válido`
    )
  },
  /**
   * Rule validate phone number
   * @param {any} v the given value to validate
   * @param {string} label
   * @returns validate
   */
  ruleTelephone: (v, label = null) => {
    return (
      !v ||
      /^((\+\d{1,3}(-| )?\(?\d\)?(-| )?\d{1,5})|(\(?\d{2,6}\)?))(-| )?(\d{3,4})(-| )?(\d{4})(( x| ext)\d{1,5}){0,1}$/.test(
        String(v)
      ) ||
      `El ${label ?? "campo"} debe ser un teléfono válido`
    )
  },
  /**
   * Rule validate number less than
   * @param {any} v the given value to validate
   * @param {any} targetValue the target value to check againt
   * @param {string} label
   * @returns validate
   */
  ruleLessThan: (v = null, targetValue = null, label = null) => {
    return (
      v == null ||
      targetValue == null ||
      parseFloat(v) < parseFloat(targetValue) ||
      `El ${label ?? "campo"} debe ser menor que ${targetValue}`
    )
  },
  /**
   * Rule validate number greater than
   * @param {any} v the given value to validate
   * @param {any} targetValue the target value to check againt
   * @param {string} label
   * @returns validate
   */
  ruleGreaterThan: (v = null, targetValue = null, label = null) => {
    return (
      v == null ||
      targetValue == null ||
      parseFloat(v) > parseFloat(targetValue) ||
      `El ${label ?? "campo"} debe ser mayor que ${targetValue}`
    )
  },
  /**
   * Rule validate integer number
   * @param {any} v the given value to validate
   * @param {string} label
   * @returns validate
   */
  ruleNumber: (v, label = null) => {
    return (
      Number.isInteger(Number(v)) || `El ${label ?? "campo"} debe ser un número`
    )
  },

  /**
   * Rule validate aspect ratio in format num1/num2
   * @param {any} v the given value to validate
   * @param {string} label
   * @returns validate
   */
  ruleAspectRatio: (v, label = null) => {
    return (
      !v ||
      /^(\d+)\/(\d+)$/.test(String(v)) ||
      `El ${label ?? "campo"} debe ser un ratio en formato num1/num2`
    )
  },

  /**
   * Rule validate time range format HH:mm-HH:mm
   * @param {any} v the given value to validate
   * @param {string} label
   * @returns validate
   */
  ruleTimeRange: (v, label = null) => {
    return (
      !v ||
      /^([01]?[0-9]|2[0-3]):[0-5][0-9]-([01]?[0-9]|2[0-3]):[0-5][0-9]$/.test(
        String(v)
      ) ||
      `El ${
        label ?? "campo"
      } debe ser un rango de tiempo válido en formato HH:mm-HH:mm`
    )
  },
}
