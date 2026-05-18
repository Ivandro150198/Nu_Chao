package com.ecommerce.cod.repository;

import com.ecommerce.cod.model.Usuario;
import com.ecommerce.cod.enums.TipoUsuario;
import org.springframework.data.jpa.repository.JpaRepository;
import org.springframework.data.jpa.repository.Query;
import org.springframework.data.repository.query.Param;
import org.springframework.stereotype.Repository;

import java.util.List;
import java.util.Optional;

@Repository
public interface UsuarioRepository extends JpaRepository<Usuario, Long> {

    Optional<Usuario> findByEmail(String email);

    Optional<Usuario> findByTelefone(String telefone);

    boolean existsByEmail(String email);

    boolean existsByTelefone(String telefone);

    List<Usuario> findByTipoUsuarioAndAtivoTrue(TipoUsuario tipoUsuario);

    @Query("SELECT u FROM Usuario u WHERE u.ativo = true AND (LOWER(u.nomeCompleto) LIKE LOWER(CONCAT('%', :termo, '%')) OR LOWER(u.email) LIKE LOWER(CONCAT('%', :termo, '%')) OR u.telefone LIKE CONCAT('%', :termo, '%'))")
    List<Usuario> buscarPorTermo(@Param("termo") String termo);

    @Query("SELECT u FROM Usuario u WHERE u.tipoUsuario = :tipoUsuario AND u.ativo = true ORDER BY u.criadoEm DESC")
    List<Usuario> buscarPorTipoOrdenadoPorDataCriacao(@Param("tipoUsuario") TipoUsuario tipoUsuario);
}
